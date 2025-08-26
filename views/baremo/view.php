<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper;

/** @var yii\web\View $this */
/** @var app\models\Baremo $model */
/** @var int $clinica_id // Se asume que el ID de la clínica se pasa a esta vista */
/** @var app\models\RmClinica $clinica // Se asume que el modelo de la clínica también se pasa */

// --- CORRECCIÓN DE RECUPERACIÓN Y VALIDACIÓN DE $clinica_id ---
// Si $clinica_id no se pasa como variable, intenta obtenerlo de la URL
if (!isset($clinica_id)) {
    $clinica_id = Yii::$app->request->get('clinica_id');
}

// Asegúrate de que $clinica_id sea un valor válido antes de intentar buscar la clínica.
// Si $clinica_id es nulo o vacío, inicializa $clinica con valores por defecto.
if (!empty($clinica_id)) {
    $clinica = \app\models\RmClinica::findOne((int)$clinica_id); // Castear a int para evitar errores de tipo
    if (!$clinica) {
        // Fallback si la clínica no se encuentra (por ejemplo, ID inválido)
        $clinica = (object)['id' => (int)$clinica_id, 'nombre' => 'Clínica Desconocida'];
    }
} else {
    // Si no hay clinica_id, inicializa $clinica con valores nulos para evitar errores de propiedad
    $clinica = (object)['id' => null, 'nombre' => 'Clínica Desconocida'];
}


$this->title = 'DETALLES DEL BAREMO: ' . Html::encode($model->nombre_servicio);

$rol = UserHelper::getMyRol();
$canManage = ($rol == 'superadmin');

// --- BREADCRUMBS CORREGIDOS ---


if($canManage == true){
$this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['/rm-clinica/index']];
};


if ($clinica->id !== null) { // Solo si tenemos un ID de clínica válido
    $this->params['breadcrumbs'][] = ['label' => Html::encode($clinica->nombre), 'url' => ['/rm-clinica/view', 'id' => $clinica->id]];
    $this->params['breadcrumbs'][] = ['label' => 'BAREMOS', 'url' => ['index', 'clinica_id' => $clinica->id]]; // Enlace a la lista de baremos de esta clínica
} else {
    $this->params['breadcrumbs'][] = ['label' => 'BAREMOS', 'url' => ['index']]; // Enlace general si no hay clinica_id
}

$this->params['breadcrumbs'][] = Html::encode($model->nombre_servicio); // Miga de pan del servicio actual

\yii\web\YiiAsset::register($this); // Registra los assets por defecto de Yii

// Función auxiliar para formatear fechas, manejando valores nulos
if (!function_exists('formatUpdatedAt')) { // Evita redefinir si ya existe globalmente
    function formatUpdatedAt($value) {
        if (empty($value)) {
            return 'No se ha modificado';
        }
        return Yii::$app->formatter->asDatetime($value, 'medium');
    }
}



?>

<div class="main-container"> <!-- Usando la clase 'main-container' definida en el fragmento CSS -->
   
    <!-- Encabezado y Botones de Acción Principal -->
    <div class="header-section"> <!-- Usando la clase 'header-section' definida en el fragmento CSS -->
        <h1><?= Html::encode($this->title) ?></h1>

        <div class="header-buttons-group"> <!-- Usando la clase 'header-buttons-group' definida en el fragmento CSS -->
            <?php if ($canManage) : ?>
                <?= Html::a(
                    '<i class="fas fa-edit mr-2"></i> Actualizar',
                    ['update', 'id' => $model->id],
                    ['class' => 'btn-base btn-blue'] /* Usando clases de botón definidas en el fragmento CSS */
                ) ?>
                <?= Html::a(
                    '<i class="fas fa-trash-alt mr-2"></i> Eliminar', // Icono de Font Awesome para eliminar
                    ['delete', 'id' => $model->id],
                    [
                        'class' => 'btn-base btn-red', 
                        'data' => [
                            'confirm' => '¿Estás seguro de que quieres eliminar este elemento?',
                            'method' => 'post',
                        ],
                    ]
                ) ?>
            <?php endif; ?>
            <!-- Botón "Volver" corregido para ir al índice de baremos de la clínica -->
            <?php if ($clinica->id !== null) : ?>
                <?= Html::a(
                    '<i class="fas fa-undo mr-2"></i> Volver a Baremos', // Texto más descriptivo para el botón
                    ['index', 'clinica_id' => $clinica->id], // Ahora apunta al índice de baremos de la clínica
                    [
                        'class' => 'btn-base btn-gray', /* Usando clases de botón definidas en el fragmento CSS */
                        'title' => 'Volver a la lista de baremos de esta clínica',
                    ]
                ) ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Tarjeta de Información General del Servicio (ms-panel) -->
    <div class="ms-panel border-blue"> <!-- Usando clase global de sipsa.css, con borde azul -->
        <div class="ms-panel-body"> <!-- Contenedor de cuerpo de panel -->
            <h3 class="section-title">
                <i class="fas fa-info-circle text-blue-600 mr-3"></i> Información General del Servicio
            </h3>
            <div class="info-grid g-3"> <!-- Usando info-grid para la distribución en columnas -->
                <div class="col">
                    <p><strong>Nombre del Servicio:</strong> <?= Html::encode($model->nombre_servicio) ?></p>
                    <p><strong>Área:</strong> <?= Html::encode($model->area ? $model->area->nombre : 'N/A') ?></p>
                    <p><strong>Estatus:</strong> 
                        <span class="status-badge <?= $model->estatus == "Activo" ? 'active' : 'inactive' ?>">
                            <?= $model->estatus == "Activo" ? 'Activo' : 'Inactivo' ?>
                        </span>
                    </p>
                </div>
                <div class="col">
                    <p><strong>Costo:</strong> <?= $model->costo ?></p>
                    <p><strong>Precio:</strong> <?= $model->precio ?></p>
                </div>
            </div>
            
            <div class="border-top-section mt-4 pt-4"> <!-- Separador y padding superior -->
                <h3 class="section-title">
                    <i class="fas fa-align-left text-gray-600 mr-3"></i> Descripción del Servicio
                </h3>
                <p><?= nl2br(Html::encode($model->descripcion)) ?></p>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Fechas de Gestión (ms-panel) -->
    <div class="ms-panel border-gray"> <!-- Usando clase global de sipsa.css, con borde gris -->
        <div class="ms-panel-body"> <!-- Contenedor de cuerpo de panel -->
            <h3 class="section-title">
                <i class="fas fa-calendar-alt text-gray-600 mr-3"></i> Fechas de Gestión
            </h3>
            <div class="info-grid g-3"> <!-- Usando info-grid para la distribución en columnas -->
                <div class="col">
                    <div class="inner-card-section"> <!-- Usando la clase 'inner-card-section' definida en el fragmento CSS -->
                        <h6>Fecha de Creación</h6>
                        <p><?= Html::encode(Yii::$app->formatter->asDatetime($model->created_at, 'medium')) ?></p>
                    </div>
                </div>
                <div class="col">
                    <div class="inner-card-section"> <!-- Usando la clase 'inner-card-section' definida en el fragmento CSS -->
                        <h6>Última Actualización</h6>
                        <p><?= Html::encode(formatUpdatedAt($model->updated_at)) ?></p>
                    </div>
                </div>
                <div class="col-xl-12"> <!-- Ocupa todo el ancho en pantallas extra-grandes -->
                    <div class="inner-card-section">
                        <h6>Eliminado en</h6>
                        <p><?= $model->deleted_at ? Html::encode(Yii::$app->formatter->asDatetime($model->deleted_at, 'medium')) : 'N/A' ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
