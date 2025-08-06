<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper;
use app\models\RmClinica; // Importar el modelo de la clínica

/** @var yii\web\View $this */
/** @var app\models\UserDatos $model */
/** @var string $estado Nombre del estado resuelto para mostrar */
/** @var string $municipio Nombre del municipio resuelto para mostrar */
/** @var string $parroquia Nombre de la parroquia resuelta para mostrar */
/** @var string $ciudad Nombre de la ciudad resuelta para mostrar */
/** @var int|null $clinica_id // Se asume que este parámetro puede venir de la URL */

// --- Detección y Carga de Clínica (para contexto) ---
// La vista solo se considerará en el contexto de una clínica si el 'clinica_id' viene explícitamente en la URL.
$clinica = null;
$clinica_id_from_url = Yii::$app->request->get('clinica_id'); 

if (!empty($clinica_id_from_url)) {
    $clinica = RmClinica::findOne((int)$clinica_id_from_url);
    if (!$clinica) {
        // Fallback si la clínica del ID de la URL no se encuentra
        $clinica = (object)['id' => (int)$clinica_id_from_url, 'nombre' => 'Clínica Desconocida'];
    }
}
// Si $clinica_id_from_url está vacío, $clinica permanece nulo, lo que activa la ruta de navegación general.
// No se intenta obtener clinica_id de $model->clinica_id para determinar el contexto de navegación.

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'Agente' || $rol == 'Asesor');

// --- Título y BREADCRUMBS CONDICIONALES ---
$this->title = 'PERFIL DEL AFILIADO: ' . Html::encode($model->nombres . ' ' . $model->apellidos);

// Siempre se muestra la raíz de clínicas
$this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['/rm-clinica/index']];

// La miga de pan de la clínica y el enlace a 'AFILIADOS' son condicionales al contexto de la clínica
if ($clinica && $clinica->id !== null) {
    $this->params['breadcrumbs'][] = ['label' => Html::encode($clinica->nombre), 'url' => ['/rm-clinica/view', 'id' => $clinica->id]];
    $this->params['breadcrumbs'][] = ['label' => 'AFILIADOS', 'url' => ['index-clinicas', 'clinica_id' => $clinica->id]]; // Enlace a la lista de afiliados de esta clínica
} else {
    // Si no estamos en el contexto de una clínica, miga de pan genérica a la lista principal
    $this->params['breadcrumbs'][] = ['label' => 'AFILIADOS', 'url' => ['index']];
}
$this->params['breadcrumbs'][] = Html::encode($model->nombres . ' ' . $model->apellidos); // Último elemento como texto

\yii\web\YiiAsset::register($this); // Registra los assets por defecto de Yii

// Función para formatear fechas y horas (si no está disponible globalmente)
if (!function_exists('formatDateTime')) {
    function formatDateTime($value) {
        return $value ? Yii::$app->formatter->asDatetime($value) : 'N/A';
    }
}
?>

<div class="main-container"> <!-- Contenedor principal de la vista -->

    <!-- Encabezado y Botones de Acción Principal -->
    <div class="header-section"> 
        <h1><?= Html::encode($this->title) ?></h1> 
        <div class="header-buttons-group">
            <?php if ($permisos): ?>
                <?= Html::a(
                    '<i class="fas fa-file-pdf mr-2"></i> Contrato',
                    ['user-datos/generar-contratov', 'id' => $model->id],
                    [
                        'class' => 'btn-base btn-red', // Usando btn-base y btn-red para el contrato PDF
                        'target' => '_blank',
                        'data-pjax' => '0'
                    ]
                ) ?>
                <?= Html::a(
                    '<i class="fas fa-edit mr-2"></i> Actualizar',
                    // Pasa clinica_id si está disponible para mantener el contexto en el update
                    Url::to(array_merge(['update', 'id' => $model->id], ($clinica && $clinica->id !== null ? ['clinica_id' => $clinica->id] : []))),
                    ['class' => 'btn-base btn-blue']
                ) ?>
                <!-- Botón "Volver" condicional y estilizado -->
                <?php if (!empty($clinica_id_from_url)) : // Condición basada directamente en el parámetro de la URL ?>
                    <?= Html::a(
                        '<i class="fas fa-undo mr-2"></i> Volver a Afiliados', 
                        ['index-clinicas', 'clinica_id' => $clinica->id], // Vuelve a la lista de afiliados de esta clínica
                        [
                            'class' => 'btn-base btn-gray', 
                            'title' => 'Volver a la lista de afiliados de esta clínica',
                        ]
                    ) ?>
                <?php else: ?>
                    <?= Html::a(
                        '<i class="fas fa-undo mr-2"></i> Volver a Afiliados', 
                        ['index'], // Vuelve a la lista general de afiliados
                        [
                            'class' => 'btn-base btn-gray', 
                            'title' => 'Volver a la lista general de afiliados',
                        ]
                    ) ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Sección de Foto de Perfil y Datos Personales -->
    <div class="ms-panel border-blue"> <!-- Usando ms-panel y borde azul -->
        <div class="ms-panel-body">
            <!-- Contenedor de imágenes de perfil e identificación -->
            <div class="flex justify-center items-center flex-wrap gap-4 mb-4"> <!-- Usando flexbox y gap de Tailwind -->
                <div class="profile-img-container text-center"> <!-- AÑADIDO: text-center para centrar su contenido -->
                    <h6>Foto de Perfil</h6>
                    <?php if ($model->selfie): ?>
                        <?= Html::img( $model->selfie, [
                            'alt' => 'Foto de Perfil',
                            'class' => 'profile-img rounded-full w-32 h-32 object-cover border-2 border-blue-400 shadow-md inline-block' // AÑADIDO: inline-block
                        ]) ?>
                    <?php else: ?>
                        <i class="fas fa-user-circle text-gray-400 inline-block" style="font-size: 80px;"></i> <!-- AÑADIDO: inline-block -->
                        <p class="text-muted mt-2">No hay selfie</p>
                    <?php endif; ?>
                </div>
                <div class="profile-img-container text-center"> <!-- AÑADIDO: text-center para centrar su contenido -->
                    <h6>Imagen de Identificación</h6>
                    <?php if ($model->imagen_identificacion): ?>
                        <?= Html::img($model->imagen_identificacion, [
                            'alt' => 'Imagen de Identificación',
                            'class' => 'profile-img w-32 h-32 object-cover border-2 border-blue-400 shadow-md inline-block' // AÑADIDO: inline-block
                        ]) ?>
                    <?php else: ?>
                        <i class="fas fa-id-card text-gray-400 inline-block" style="font-size: 80px;"></i> <!-- AÑADIDO: inline-block -->
                        <p class="text-muted mt-2">No hay imagen de identificación</p>
                    <?php endif; ?>
                </div>
            </div>
            
            <h3 class="section-title justify-center"> <!-- justify-center para centrar el título -->
                <i class="fas fa-address-card text-blue-600 mr-3"></i> Datos Personales
            </h3>
            <div class="info-grid text-left">
                <div>
                    <p><strong>Nombres:</strong> <?= Html::encode($model->nombres ?? 'N/A') ?></p>
                    <p><strong>Cédula de Identidad:</strong> <?= Html::encode(($model->tipo_cedula ? $model->tipo_cedula . '-' : '') . ($model->cedula ?? 'N/A')) ?></p>
                    <p><strong>Sexo:</strong> <?= Html::encode($model->sexo ?? 'N/A') ?></p>
                    <p><strong>Correo Electrónico:</strong> <?= !empty($model->email) ? Html::a(Html::encode($model->email), 'mailto:' . Html::encode($model->email), ['class' => 'text-blue-500']) : 'N/A' ?></p>
                </div>
                <div>
                    <p><strong>Apellidos:</strong> <?= Html::encode($model->apellidos ?? 'N/A') ?></p>
                    <p><strong>Fecha de Nacimiento:</strong> <span class="font-medium"><?= Html::encode(Yii::$app->formatter->asDate($model->fechanac, 'd-m-Y') ?? 'N/A') ?></span></p>
                    <p><strong>Teléfono:</strong> <?= Html::encode($model->telefono ?? 'N/A') ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Ubicación -->
    <div class="ms-panel border-indigo"> <!-- Usando ms-panel y borde índigo -->
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-map-marker-alt text-indigo-600 mr-3"></i> Ubicación
            </h3>
            <div class="info-grid">
                <div>
                    <p><strong>Estado:</strong> <?= Html::encode($estado ?? 'N/A') ?></p>
                    <p><strong>Ciudad:</strong> <?= Html::encode($ciudad ?? 'N/A') ?></p>
                </div>
                <div>
                    <p><strong>Municipio:</strong> <?= Html::encode($municipio ?? 'N/A') ?></p>
                    <p><strong>Parroquia:</strong> <?= Html::encode($parroquia ?? 'N/A') ?></p>
                </div>
            </div>
            <p class="border-top-section mt-4 pt-4"><strong>Dirección:</strong> <?= nl2br(Html::encode($model->direccion ?? 'N/A')) ?></p>
        </div>
    </div>

    <!-- Tarjeta de Información Adicional -->
    <div class="ms-panel border-gray"> <!-- Usando ms-panel y borde gris -->
        <div class="ms-panel-body">
            <h3 class="section-title">
                <i class="fas fa-info-circle text-gray-600 mr-3"></i> Información Adicional
            </h3>
            <div class="info-grid">
                <div>
                    <p><strong>Clínica:</strong> <?= Html::encode($model->clinica ? $model->clinica->nombre : 'No asignada') ?></p>
                    <p><strong>Asesor:</strong> <?= Html::encode($model->asesor ? $model->asesor->nom : 'Sin asignar') ?></p>
                    <p><strong>Tipo de Sangre:</strong> <?= Html::encode($model->tipo_sangre ?? 'N/A') ?></p>
                </div>
                <div>
                    <p><strong>Plan:</strong> <?= Html::encode($model->plan ? $model->plan->nombre : 'No asignado') ?></p>
                    <?php
                        $estatusText = $model->estatus ?? 'N/A';
                        $estatusClass = 'inactive';
                        if ($estatusText === 'Activo' || $estatusText === 'Registrado') { // Asume 'Registrado' también es un estado "activo" para el badge
                            $estatusClass = 'active';
                        }
                    ?>
                    <p><strong>Estatus:</strong> <span class="status-badge <?= $estatusClass ?>"><?= Html::encode($estatusText) ?></span></p>
                </div>
            </div>
        </div>
    </div>
</div>