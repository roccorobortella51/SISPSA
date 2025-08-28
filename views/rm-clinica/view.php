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
$parroquiaList = $parquiaList ?? [];
$ciudadesList = $ciudadesList ?? [];
$listaEstatus = $listaEstatus ?? [];

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'GERENTE-COMERCIALIZACION' || $rol == 'Administrador-clinica');

$permisos2 = ($rol == 'superadmin' || $rol == 'GERENTE-COMERCIALIZACION' || $rol == 'Asesor' || $rol == 'Agente' || $rol == "ADMISIÓN" || $rol == "CONTROL DE CITAS" || $rol == "COORDINADOR-CLINICA");

$permisos = false;

if ($rol == 'superadmin') 
{
    $permisos = true;
}

$this->title = 'DETALLES DE LA CLÍNICA: ' . Html::encode($model->nombre);
if($permisos == true){
$this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['index']];
}
$this->params['breadcrumbs'][] = Html::encode($model->nombre);

\yii\web\YiiAsset::register($this); // Esto registra los assets por defecto de Yii

// Función auxiliar para formatear fechas, manejando valores nulos
function formatUpdatedAt($value) {
    if (empty($value)) {
        return 'No se ha modificado';
    }
    // Asume que updated_at es un timestamp o fecha válida que Yii puede formatear
    return Yii::$app->formatter->asDatetime($value, 'medium');
}



?>

<div class="main-container"> 
   

    <!-- Encabezado y Botones de Acción Principal -->
    <div class="header-section"> 
        <h1><?= Html::encode($this->title) ?></h1>

        <div class="header-buttons-group"> 
            <?php

            if($permisos == true){
             echo Html::a(
                '<i class="fas fa-edit mr-2"></i> Actualizar',
                ['update', 'id' => $model->id],
                ['class' => 'btn-base btn-blue']
            );

            echo Html::a(
                    '<i class="fas fa-undo mr-2"></i> Volver',
                    '#',
                    [
                        'class' => 'btn-base btn-gray', /* Usando clases de botón definidas en el fragmento CSS */
                        'onclick' => 'window.history.back(); return false;',
                        'title' => 'Volver a la página anterior',
                    ]
                );          

            } ?>
           
            
        </div>
    </div>

    <?php if($permisos) {?>
        <!-- Sección de Botones de Navegación Específicos de Clínica -->
        <div class="nav-buttons-grid"> 
            <div>
                <?= Html::a(
                    '<i class="fas fa-file-invoice-dollar mr-2"></i> Baremo',
                    ['baremo/index', 'clinica_id' => $model->id],
                    ['class' => 'nav-btn-base nav-btn-blue'] /* Usando las nuevas clases de botones de navegación */
                ) ?>
            </div>
            <div>
                <?= Html::a(
                    '<i class="fas fa-clipboard-list mr-2"></i> Planes',
                    ['planes/index', 'clinica_id' => $model->id],
                    ['class' => 'nav-btn-base nav-btn-indigo'] 
                ) ?>
            </div>
            <div>
                <?= Html::a(
                    '<i class="fas fa-users mr-2"></i> Afiliados',
                    ['user-datos/index-clinicas', 'clinica_id' => $model->id],
                    ['class' => 'nav-btn-base nav-btn-teal'] 
                ) ?>
            </div>
            <div>
                <?= Html::a(
                    '<i class="fas fa-tasks mr-2"></i> Check List',
                    ['check-list-clinicas/index', 'clinica_id' => $model->id],
                    ['class' => 'nav-btn-base nav-btn-cyan'] /* Usando las nuevas clases de botones de navegación */
                ) ?>
            </div>
        </div>
    <?php } ?>

    <?php if($permisos2) {?>
        <!-- Sección de Botones de Navegación Específicos de Clínica -->
        <div class="nav-buttons-grid"> 
            <div align="center">
                <?= Html::a(
                    '<i class="fas fa-file-medical"></i> Siniestros de la Clínica',
                    ['sis-siniestro/por-clinica', 'clinica_id' => $model->id],
                    ['class' => 'nav-btn-base nav-btn-indigo'] /* Usando las nuevas clases de botones de navegación */
                ) ?>
            </div>
        </div>
    <?php } ?>

    <!-- Tarjeta de Información General de la Clínica -->
    <div class="info-card info-card-border-blue"> <!-- Usando las clases 'info-card' y 'info-card-border-blue' definidas en el fragmento CSS -->
        <h3>
            <i class="fas fa-hospital-alt text-blue-600 mr-3"></i> Información General de la Clínica
        </h3>
        <div class="info-grid"> <!-- Usando la clase 'info-grid' definida en el fragmento CSS -->
            <div>
                <h5><strong>Nombre:</strong> <?= Html::encode($model->nombre) ?></h5>
                <h5><strong>RIF:</strong> <?= Html::encode($model->rif) ?></h5>
            </div>
            <div>
                <h5><strong>Teléfono:</strong> <?= Html::encode($model->telefono) ?></h5>
                <h5><strong>Correo Electrónico:</strong> <?= Html::a(Html::encode($model->correo), 'mailto:' . Html::encode($model->correo), ['class' => 'text-blue-500']) ?></h5>
            </div>
        </div>
        <div class="info-grid border-top-section"> <!-- Usando la clase 'border-top-section' definida en el fragmento CSS -->
            <div>
                <h5><strong>Código de Clínica:</strong> <?= Html::encode($model->codigo_clinica) ?></h5>
            </div>
            <div>
                <h5><strong>Estatus:</strong> <span class="status-badge <?= $model->estatus == 'Activo' ? 'active' : 'inactive' ?>"><?= Html::encode($listaEstatus[$model->estatus] ?? $model->estatus) ?></span></h5>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Ubicación Geográfica -->
    <div class="info-card info-card-border-indigo"> <!-- Usando las clases 'info-card' y 'info-card-border-indigo' definidas en el fragmento CSS -->
        <h3>
            <i class="fas fa-map-marker-alt text-indigo-600 mr-3"></i> Ubicación Geográfica
        </h3>
        <div class="info-grid"> <!-- Usando la clase 'info-grid' definida en el fragmento CSS -->
            <div>
                <h5><strong>Estado:</strong> <?= Html::encode($estadosList[$model->estado] ?? 'N/A') ?></h5>
                <h5><strong>Municipio:</strong> <?= Html::encode($municipiosList[(string)$model->municipio] ?? 'N/A') ?></h5>
            </div>
            <div>
                <h5><strong>Parroquia:</strong> <?= Html::encode($parroquiaList[$model->parroquia] ?? 'N/A') ?></h5>
                <h5><strong>Ciudad:</strong> <?= Html::encode($ciudadesList[$model->ciudad] ?? 'N/A') ?></h5>
            </div>
        </div>
        <p class="border-top-section"><strong>Dirección:</strong> <?= nl2br(Html::encode($model->direccion)) ?></h5>
    </div>

    <!-- Tarjeta de Información de Contacto y Redes Sociales -->
    <div class="info-card info-card-border-yellow"> <!-- Usando las clases 'info-card' y 'info-card-border-yellow' definidas en el fragmento CSS -->
        <h3>
            <i class="fas fa-globe text-yellow-600 mr-3"></i> Web y Redes Sociales
        </h3>
        <div class="info-grid"> <!-- Usando la clase 'info-grid' definida en el fragmento CSS -->
            <div>
                <h5><strong>Página Web:</strong>
                    <?php if (!empty($model->webpage)): ?>
                        <?= Html::a(Html::encode($model->webpage), $model->webpage, ['target' => '_blank', 'class' => 'text-blue-500']) ?>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </h5>
            </div>
            <div>
                <h5><strong>Instagram:</strong>
                    <?php if (!empty($model->rs_instagram)): ?>
                        <?= Html::a(Html::encode($model->rs_instagram), 'https://instagram.com/' . ltrim($model->rs_instagram, '@'), ['target' => '_blank', 'class' => 'text-blue-500']) ?>
                    <?php else: ?>
                        N/A
                    <?php endif; ?>
                </h5>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Fechas de Gestión -->
    <div class="info-card info-card-border-gray"> <!-- Usando las clases 'info-card' y 'info-card-border-gray' definidas en el fragmento CSS -->
        <h3>
            <i class="fas fa-calendar-alt text-gray-600 mr-3"></i> Fechas de Gestión
        </h3>
        <div class="info-grid"> <!-- Usando la clase 'info-grid' definida en el fragmento CSS -->
            <div class="inner-card-section"> <!-- Usando la clase 'inner-card-section' definida en el fragmento CSS -->
                <h6>Fecha de Creación</h6>
                <h5><?= Html::encode(Yii::$app->formatter->asDatetime($model->created_at, 'medium')) ?></h5>
            </div>
            <div class="inner-card-section"> <!-- Usando la clase 'inner-card-section' definida en el fragmento CSS -->
                <h6>Última Actualización</h6>
                <h5><?= Html::encode(formatUpdatedAt($model->updated_at)) ?></h5>
            </div>
        </div>
    </div>
</div>
