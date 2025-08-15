<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper;
use app\models\RmClinica;
use app\models\AgenteFuerza;


/** @var yii\web\View $this */
/** @var app\models\UserDatos $model */
/** @var string $estado Nombre del estado resuelto para mostrar */
/** @var string $municipio Nombre del municipio resuelto para mostrar */
/** @var string $parroquia Nombre de la parroquia resuelta para mostrar */
/** @var string $ciudad Nombre de la ciudad resuelta para mostrar */
/** @var int|null $clinica_id // Se asume que este parámetro puede venir de la URL */

// --- Detección y Carga de Clínica (para contexto) ---
$clinica = null;
$clinica_id_from_url = Yii::$app->request->get('clinica_id'); 

if (!empty($clinica_id_from_url)) {
    $clinica = RmClinica::findOne((int)$clinica_id_from_url);
    if (!$clinica) {
        $clinica = (object)['id' => (int)$clinica_id_from_url, 'nombre' => 'Clínica Desconocida'];
    }
}

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin' || $rol == 'GERENTE-COMERCIALIZACION' || $rol == 'Asesor');

// --- Título y BREADCRUMBS CONDICIONALES ---
$titulo = 'PERFIL DEL AFILIADO: '. 
$model->nombres . ' ' . $model->apellidos ;

$asesor = AgenteFuerza::find()->where(['id' => $model->asesor_id])->one();

$titulo2 = "";
if($asesor){
$titulo2 = 'ASESOR: ' . 
$asesor->id . ' - ' . 
$asesor->userDatos->nombres . " " . 
$asesor->userDatos->apellidos .
 " (" . $asesor->userDatos->user->username . ")";
}

$this->title = $titulo;

// Siempre se muestra la raíz de clínicas
$this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['/rm-clinica/index']];

// La miga de pan de la clínica y el enlace a 'AFILIADOS' son condicionales al contexto de la clínica
if ($clinica && $clinica->id !== null) {
    $this->params['breadcrumbs'][] = ['label' => Html::encode($clinica->nombre), 'url' => ['/rm-clinica/view', 'id' => $clinica->id]];
    $this->params['breadcrumbs'][] = ['label' => 'AFILIADOS', 'url' => ['index-clinicas', 'clinica_id' => $clinica->id]];
} else {
    $this->params['breadcrumbs'][] = ['label' => 'AFILIADOS', 'url' => ['index']];
}
$this->params['breadcrumbs'][] = Html::encode($model->nombres . ' ' . $model->apellidos);

\yii\web\YiiAsset::register($this);

if (!function_exists('formatDateTime')) {
    function formatDateTime($value) {
        return $value ? Yii::$app->formatter->asDatetime($value) : 'N/A';
    }
}

// --- DATOS DEL PLAN Y CONSUMO (CORREGIDO) ---
$nombrePlan = $model->plan->nombre ?? 'N/A';
$precioPlan = $model->plan->precio_dolar ?? 0;
$saldoInicial = $model->plan->cobertura_dolar ?? 0;
$consumoActual = 1200.50; // ¡AQUÍ DEBES USAR EL DATO REAL DE TU BBDD!
$saldoDisponible = $saldoInicial - $consumoActual;

// Manejo del error de división por cero
$porcentajeConsumido = 0;
if ($saldoInicial > 0) {
    $porcentajeConsumido = round(($consumoActual / $saldoInicial) * 100);
}

// Función auxiliar para mostrar Sí/No con íconos y clases de CSS
function formatBooleanIcon($value) {
    if ($value) {
        return '<span class="text-green-600 mr-1"><i class="fas fa-check-circle"></i></span> Sí';
    } else {
        return '<span class="text-red-600 mr-1"><i class="fas fa-times-circle"></i></span> No';
    }
}

?>

<div class="main-container user-profile-view">

    <div class="header-section">
        <h1><?= Html::encode($this->title) ?></h1>
        <h1><?= Html::encode($titulo2) ?></h1>
        <div class="header-buttons-group">
            <?php if ($permisos): ?>
                <?= Html::a(
                    '<i class="fas fa-file-pdf mr-2"></i> Contrato',
                    ['user-datos/generar-contratov', 'id' => $model->id],
                    [
                        'class' => 'btn-base btn-red',
                        'target' => '_blank',
                        'data-pjax' => '0'
                    ]
                ) ?>
                <?= Html::a(
                    '<i class="fas fa-edit mr-2"></i> Actualizar',
                    Url::to(array_merge(['update', 'id' => $model->id], ($clinica && $clinica->id !== null ? ['clinica_id' => $clinica->id] : []))),
                    ['class' => 'btn-base btn-blue']
                ) ?>
                <?php if (!empty($clinica_id_from_url)) : ?>
                    <?= Html::a(
                        '<i class="fas fa-undo mr-2"></i> Volver a Afiliados', 
                        ['index-clinicas', 'clinica_id' => $clinica->id],
                        [
                            'class' => 'btn-base btn-gray', 
                            'title' => 'Volver a la lista de afiliados de esta clínica',
                        ]
                    ) ?>
                <?php else: ?>
                    <?= Html::a(
                        '<i class="fas fa-undo mr-2"></i> Volver a Afiliados', 
                        ['index'],
                        [
                            'class' => 'btn-base btn-gray', 
                            'title' => 'Volver a la lista general de afiliados',
                        ]
                    ) ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="ms-panel border-blue">
        <div class="ms-panel-body">
            <div class="flex justify-center items-center flex-wrap gap-4 mb-4">
                <div class="profile-img-container text-center">
                    <h5>Foto de Perfil</h5>
                    <?php if ($model->selfie): ?>
                        <?= Html::img( $model->selfie, [
                            'alt' => 'Foto de Perfil',
                            'class' => 'profile-img rounded-full w-32 h-32 object-cover border-2 border-blue-400 shadow-md inline-block'
                        ]) ?>
                    <?php else: ?>
                        <i class="fas fa-user-circle text-gray-400 inline-block" style="font-size: 80px;"></i>
                        <p class="text-muted mt-2">No hay selfie</h5>
                    <?php endif; ?>
                </div>
                <div class="profile-img-container text-center">
                    <h5>Imagen de Identificación</h5>
                    <?php if ($model->imagen_identificacion): ?>
                        <?= Html::img($model->imagen_identificacion, [
                            'alt' => 'Imagen de Identificación',
                            'class' => 'profile-img w-32 h-32 object-cover border-2 border-blue-400 shadow-md inline-block'
                        ]) ?>
                    <?php else: ?>
                        <i class="fas fa-id-card text-gray-400 inline-block" style="font-size: 80px;"></i>
                        <p class="text-muted mt-2">No hay imagen de identificación</h5>
                    <?php endif; ?>
                </div>
            </div>
            
            <h5 class="section-title justify-center">
                <i class="fas fa-address-card text-blue-600 mr-3"></i> Datos Personales
            </h5>
            <div class="info-grid text-left">
                <div>
                    <h5><strong>Nombres:</strong> <?= Html::encode($model->nombres ?? 'N/A') ?></h5>
                    <h5><strong>Cédula de Identidad:</strong> <?= Html::encode(($model->tipo_cedula ? $model->tipo_cedula . '-' : '') . ($model->cedula ?? 'N/A')) ?></h5>
                    <h5><strong>Sexo:</strong> <?= Html::encode($model->sexo ?? 'N/A') ?></h5>
                    <h5><strong>Correo Electrónico:</strong> <?= !empty($model->email) ? Html::a(Html::encode($model->email), 'mailto:' . Html::encode($model->email), ['class' => 'text-blue-500']) : 'N/A' ?></h5>
                </div>
                <div>
                    <h5><strong>Apellidos:</strong> <?= Html::encode($model->apellidos ?? 'N/A') ?></h5>
                    <h5><strong>Fecha de Nacimiento:</strong> <span class="font-medium"><?= Html::encode(Yii::$app->formatter->asDate($model->fechanac, 'd-m-Y') ?? 'N/A') ?></span></h5>
                    <h5><strong>Teléfono:</strong> <?= Html::encode($model->telefono ?? 'N/A') ?></h5>
                </div>
            </div>
        </div>
    </div>

    <div class="ms-panel border-purple">
        <div class="ms-panel-body">
            <h5 class="section-title text-center">
                <i class="fas fa-hand-holding-usd text-purple-600 mr-3"></i> Detalles del Plan
            </h5>
            
            <div class="info-grid-2x2">
                <div class="info-card-body">
                    <h5>Plan</h5>
                    <p class="h5"><?= Html::encode($nombrePlan) ?></h5>
                </div>
                <div class="info-card-body">
                    <h5>Precio</h5>
                    <p class="h5 text-info"><?= Yii::$app->formatter->asCurrency($precioPlan, 'USD') ?></h5>
                </div>
                <div class="info-card-body">
                    <h5>Cobertura Total</h5>
                    <p class="h5 text-success"><?= Yii::$app->formatter->asCurrency($saldoInicial, 'USD') ?></h5>
                </div>
                <div class="info-card-body">
                    <h5>Consumido</h5>
                    <p class="h5 text-danger"><?= Yii::$app->formatter->asCurrency($consumoActual, 'USD') ?></h5>
                </div>
            </div>

            <hr class="my-4">

            <div class="balance-available">
                <i class="fas fa-piggy-bank text-blue-600"></i>
                <h5>Saldo Disponible</h5>
                <p class="h5 font-weight-bold text-blue-600"><?= Yii::$app->formatter->asCurrency($saldoDisponible, 'USD') ?></h5>
            </div>

            <div class="progress-bar-container mt-4">
                <div class="progress-bar" style="width: <?= $porcentajeConsumido ?>%; background-color: #6c757d;"></div>
            </div>
            <p class="text-sm text-center text-muted mt-2">
                <?= $porcentajeConsumido ?>% del plan consumido.
            </h5>
        </div>
    </div>

    <div class="ms-panel border-indigo">
        <div class="ms-panel-body">
            <h5 class="section-title">
                <i class="fas fa-map-marker-alt text-indigo-600 mr-3"></i> Ubicación
            </h5>
            <div class="info-grid">
                <div>
                    <h5><strong>Estado:</strong> <?= Html::encode($estado ?? 'N/A') ?></h5>
                    <h5><strong>Ciudad:</strong> <?= Html::encode($ciudad ?? 'N/A') ?></h5>
                </div>
                <div>
                    <h5><strong>Municipio:</strong> <?= Html::encode($municipio ?? 'N/A') ?></h5>
                    <h5><strong>Parroquia:</strong> <?= Html::encode($parroquia ?? 'N/A') ?></h5>
                </div>
            </div>
            <p class="border-top-section mt-4 pt-4"><strong>Dirección:</strong> <?= nl2br(Html::encode($model->direccion ?? 'N/A')) ?></h5>
        </div>
    </div>

    <div class="ms-panel border-gray">
        <div class="ms-panel-body">
            <h5 class="section-title">
                <i class="fas fa-info-circle text-gray-600 mr-3"></i> Información Adicional
            </h5>
            <div class="info-grid">
                <div>
                    <h5><strong>Clínica:</strong> <?= Html::encode($model->clinica ? $model->clinica->nombre : 'No asignada') ?></h5>
                    <h5><strong>Agencia:</strong> <?= Html::encode($model->asesor ? $model->asesor->agente->nom : 'Sin asignar') ?></h5>
                    <h5><strong>Asesor:</strong> <?= Html::encode($model->asesor ? $model->asesor->userDatos->nombres . " " . $model->asesor->userDatos->apellidos . " (" . $model->asesor->userDatos->user->username . ")" : 'Sin asignar') ?></h5>
                    <h5><strong>Tipo de Sangre:</strong> <?= Html::encode($model->tipo_sangre ?? 'N/A') ?></h5>
                </div>
                <div>
                    <h5><strong>Plan:</strong> <?= Html::encode($model->plan ? $model->plan->nombre : 'No asignado') ?></h5>
                    <?php
                        $estatusText = $model->estatus ?? 'N/A';
                        $estatusClass = 'inactive';
                        if ($estatusText === 'Activo' || $estatusText === 'Registrado') {
                            $estatusClass = 'active';
                        }
                    ?>
                    <h5><strong>Estatus:</strong> <span class="status-badge <?= $estatusClass ?>"><?= Html::encode($estatusText) ?></span></h5>
                </div>
            </div>
        </div>
    </div>
</div>

