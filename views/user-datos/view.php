<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper;
use app\models\RmClinica;
use app\models\AgenteFuerza;
use app\components\SaldoHelper; // Importar el helper

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

// --- USAR LA FUNCIÓN GLOBAL PARA CALCULAR SALDO ---
$datosSaldo = SaldoHelper::calcularSaldoDisponible($model->id);
$precioPlan = $datosSaldo['precio_plan'];
$coberturaPlan = $datosSaldo['cobertura_plan'];
$consumoActual = $datosSaldo['sumatoria_siniestros'];
$saldoDisponible = $datosSaldo['saldo_disponible'];
$porcentajeConsumido = $datosSaldo['porcentaje_consumido'];
$baseCalculo = $datosSaldo['base_calculo'];

// Obtener historial de siniestros
$historialSiniestros = SaldoHelper::obtenerHistorialSiniestros($model->id);

// Función auxiliar para mostrar Sí/No con íconos y clases de CSS
function formatBooleanIcon($value) {
    if ($value) {
        return '<span class="text-green-600 mr-1"><i class="fas fa-check-circle"></i></span> Sí';
    } else {
        return '<span class="text-red-600 mr-1"><i class="fas fa-times-circle"></i></span> No';
    }
}
$currentRoute = Yii::$app->controller->getRoute();
?>

<div class="row row-cols-1 row-cols-md-4 justify-content-center g-3 mb-4">
    <div class="col"> <h4><?= Html::a('<i class="fas fa-user"></i> Datos Personales', Url::to(['index']), [
            'class' => 'btn btn-primary btn-lg w-100 ' . ($currentRoute === 'index' ? 'active' : ''),
        ]) ?></h4>
    </div>

    <div class="col"> <h4><?= Html::a('<i class="fas fa-phone-alt"></i> Contactos de Emergencia', Url::to(['contactos-emergencia/index', 'user_id' => $model->id]), [
            'class' => 'btn btn-primary btn-lg w-100 ' . ($currentRoute === 'contactos-emergencia/index' ? 'active' : ''),
            'data-pjax' => '0'
        ]) ?></h4>
    </div>

    <div class="col"> <h4><?= Html::a('<i class="fas fa-heartbeat"></i> Declaración de Salud', Url::to(['declaracion-de-salud/index', 'user_id' => $model->id]), [
            'class' => 'btn btn-primary btn-lg w-100 ' . ($currentRoute === 'declaracion-salud/index' ? 'active' : ''),
            'data-pjax' => '0'
        ]) ?></h4>
    </div>
    
    <div class="col"> <h4><?= Html::a('<i class="fas fa-file-medical"></i> Siniestros', Url::to(['sis-siniestro/index', 'user_id' => $model->id]), [
            'class' => 'btn btn-primary btn-lg w-100 ' . ($currentRoute === 'sis-siniestro/index' ? 'active' : ''),
            'data-pjax' => '0'
        ]) ?></h4>
    </div>
</div>

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

    <div class="balance-available">
                <i class="fas fa-piggy-bank text-blue-600"></i>
                <h4>Saldo Disponible</h4>
                <p class="h5 font-weight-bold text-blue-600"><?= Yii::$app->formatter->asCurrency($saldoDisponible, 'USD') ?></h5>
    </div>

     <hr class="my-4">

            

            <div class="progress-bar-container mt-4">
                <div class="progress-bar" style="width: <?= $porcentajeConsumido ?>%; background-color: <?= $porcentajeConsumido > 80 ? '#dc3545' : ($porcentajeConsumido > 50 ? '#ffc107' : '#28a745') ?>;"></div>
            </div>
            <p class="text-sm text-center text-muted mt-2">
                <?= $porcentajeConsumido ?>% del plan consumido.
            </h5>

    <div class="ms-panel border-blue">
        <div class="ms-panel-body">
            <div style="display: flex; justify-content: center; align-items: center; width: 100%; padding: 5rem 0;">
                <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; justify-content: center; align-items: center; width: 100%;">
                    <div style="text-align: center; display: flex; flex-direction: column; align-items: center; padding: 2.5rem 0;">
                        <h5>Foto de Perfil</h5>
                        <?php if ($model->selfie): ?>
                            <?= Html::img( $model->selfie, [
                                'alt' => 'Foto de Perfil',
                                'class' => 'profile-img rounded-full w-32 h-32 object-cover border-2 border-blue-400 shadow-md'
                            ]) ?>
                        <?php else: ?>
                            <i class="fas fa-user-circle text-gray-400" style="font-size: 80px;"></i>
                            <p class="text-muted mt-2">No hay selfie</p>
                        <?php endif; ?>
                    </div>
                    <div style="text-align: center; display: flex; flex-direction: column; align-items: center; padding: 2.5rem 0;">
                        <h5>Imagen de Identificación</h5>
                        <?php if ($model->imagen_identificacion): ?>
                            <?= Html::img($model->imagen_identificacion, [
                                'alt' => 'Imagen de Identificación',
                                'class' => 'profile-img w-32 h-32 object-cover border-2 border-blue-400 shadow-md'
                            ]) ?>
                        <?php else: ?>
                            <i class="fas fa-id-card text-gray-400" style="font-size: 80px;"></i>
                            <p class="text-muted mt-2">No hay imagen de identificación</p>
                        <?php endif; ?>
                    </div>
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
                    <p class="h5"><?= Html::encode($model->plan->nombre ?? 'N/A') ?></h5>
                </div>
                <div class="info-card-body">
                    <h5>Cuota mensual</h5>
                    <p class="h5 text-info"><?= Yii::$app->formatter->asCurrency($precioPlan, 'USD') ?></h5>
                </div>
                <div class="info-card-body">
                    <h5>Cobertura</h5>
                    <p class="h5 text-info"><?= Yii::$app->formatter->asCurrency($coberturaPlan, 'USD') ?></h5>
                </div>
                <div class="info-card-body">
                    <h5>Saldo Disponible</h5>
                    <p class="h5 text-success"><?= Yii::$app->formatter->asCurrency($saldoDisponible, 'USD') ?></h5>
                </div>
                <div class="info-card-body">
                    <h5>Consumido</h5>
                    <p class="h5 text-danger"><?= Yii::$app->formatter->asCurrency($consumoActual, 'USD') ?></h5>
                </div>
            </div>

           
            
            <!-- Historial de Siniestros (Opcional) -->
            <?php if (!empty($historialSiniestros)): ?>
            <div class="mt-4">
                <h6 class="text-center"><strong>Últimos siniestros registrados:</strong></h6>
                <div class="table-responsive">
                    <table class="table  table-bordered">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Descripción</th>
                                <th>Costo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($historialSiniestros, 0, 3) as $siniestro): ?>
                            <tr>
                                <td><?= Yii::$app->formatter->asDate($siniestro->fecha) ?></td>
                                <td><?= Html::encode(mb_substr($siniestro->descripcion, 0, 30) . (mb_strlen($siniestro->descripcion) > 30 ? '...' : '')) ?></td>
                                <td class="text-right"><?= Yii::$app->formatter->asCurrency($siniestro->costo_total, 'USD') ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php if (count($historialSiniestros) > 3): ?>
                <p class="text-center">
                    <small><?= count($historialSiniestros) - 3 ?> siniestros más...</small>
                </p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
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
    <div class="ms-panel border-green">
    <div class="ms-panel-body">
        <h5 class="section-title">
            <i class="fas fa-file-alt text-green-600 mr-3"></i> Información Sociodemográfica y Económica
        </h5>
        <div class="info-grid text-left">
            <div>
                <h5><strong>Nacionalidad:</strong> <?= Html::encode($model->nacionalidad ?? 'N/A') ?></h5>
                <h5><strong>Estado Civil:</strong> <?= Html::encode($model->estado_civil ?? 'N/A') ?></h5>
                <h5><strong>Lugar de Nacimiento:</strong> <?= Html::encode($model->lugar_nacimiento ?? 'N/A') ?></h5>
                <h5><strong>Profesión:</strong> <?= Html::encode($model->profesion ?? 'N/A') ?></h5>
                <h5><strong>Ocupación:</strong> <?= Html::encode($model->ocupacion ?? 'N/A') ?></h5>
            </div>
            <div>
                <h5><strong>Actividad Económica:</strong> <?= Html::encode($model->actividad_economica ?? 'N/A') ?></h5>
                <h5><strong>Ramo Comercial:</strong> <?= Html::encode($model->ramo_comercial ?? 'N/A') ?></h5>
                <h5><strong>Descripción de Actividad:</strong> <?= Html::encode($model->descripcion_actividad ?? 'N/A') ?></h5>
                <h5><strong>Ingreso Anual:</strong> <?= Yii::$app->formatter->asCurrency($model->ingreso_anual ?? 0, 'USD') ?></h5>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($model->telefono_residencia) || !empty($model->telefono_oficina) || !empty($model->direccion_residencia) || !empty($model->direccion_oficina)): ?>
    <div class="ms-panel border-orange">
        <div class="ms-panel-body">
            <h5 class="section-title">
                <i class="fas fa-building text-orange-600 mr-3"></i> Información de Contacto y Domicilio Adicional
            </h5>
            <div class="info-grid text-left">
                <div>
                    <h5><strong>Dirección de Residencia:</strong> <?= nl2br(Html::encode($model->direccion_residencia ?? 'N/A')) ?></h5>
                    <h5><strong>Teléfono de Residencia:</strong> <?= Html::encode($model->telefono_residencia ?? 'N/A') ?></h5>
                </div>
                <div>
                    <h5><strong>Dirección de Oficina:</strong> <?= nl2br(Html::encode($model->direccion_oficina ?? 'N/A')) ?></h5>
                    <h5><strong>Teléfono de Oficina:</strong> <?= Html::encode($model->telefono_oficina ?? 'N/A') ?></h5>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if ($model->tiene_contratante_diferente): ?>
    <div class="ms-panel border-blue">
        <div class="ms-panel-body">
            <h5 class="section-title">
                <i class="fas fa-user-tie text-blue-600 mr-3"></i> Información del Contratante
            </h5>
            <div class="info-grid text-left">
                <div>
                    <h5><strong>Nombres:</strong> <?= Html::encode($model->nombre_contratante ?? 'N/A') ?></h5>
                    <h5><strong>Apellidos:</strong> <?= Html::encode($model->apellido_contratante ?? 'N/A') ?></h5>
                    <h5><strong>Cédula:</strong> <?= Html::encode(($model->tipo_cedula_contratante ? $model->tipo_cedula_contratante . '-' : '') . ($model->cedula_contratante ?? 'N/A')) ?></h5>
                    <h5><strong>Fecha de Nacimiento:</strong> <?= Html::encode(Yii::$app->formatter->asDate($model->fecha_nacimiento_contratante, 'd-m-Y') ?? 'N/A') ?></h5>
                    <h5><strong>Email:</strong> <?= Html::a(Html::encode($model->email_contratante), 'mailto:' . Html::encode($model->email_contratante), ['class' => 'text-blue-500']) ?? 'N/A' ?></h5>
                </div>
                <div>
                    <h5><strong>Sexo:</strong> <?= Html::encode($model->sexo_contratante ?? 'N/A') ?></h5>
                    <h5><strong>Nacionalidad:</strong> <?= Html::encode($model->nacionalidad_contratante ?? 'N/A') ?></h5>
                    <h5><strong>Estado Civil:</strong> <?= Html::encode($model->estado_civil_contratante ?? 'N/A') ?></h5>
                    <h5><strong>Ocupación:</strong> <?= Html::encode($model->ocupacion_contratante ?? 'N/A') ?></h5>
                    <h5><strong>Teléfono Celular:</strong> <?= Html::encode($model->telefono_celular_contratante ?? 'N/A') ?></h5>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="ms-panel border-red">
    <div class="ms-panel-body">
        <h5 class="section-title">
            <i class="fas fa-credit-card text-red-600 mr-3"></i> Información Bancaria
        </h5>
        <div class="info-grid text-left">
            <div>
                <h5><strong>Nombre del Titular:</strong> <?= Html::encode($model->nombre_titular ?? 'N/A') ?></h5>
                <h5><strong>Cédula del Titular:</strong> <?= Html::encode($model->cedula_titular ?? 'N/A') ?></h5>
                <h5><strong>Número de Cuenta:</strong> <?= Html::encode($model->numero_cuenta ?? 'N/A') ?></h5>
            </div>
            <div>
                <h5><strong>Banco:</strong> <?= Html::encode($model->banco ?? 'N/A') ?></h5>
                <h5><strong>Tipo de Cuenta:</strong> <?= Html::encode($model->tipo_cuenta ?? 'N/A') ?></h5>
            </div>
        </div>
    </div>
</div>

<div class="ms-panel border-gray">
    <div class="ms-panel-body">
        <h5 class="section-title">
            <i class="fas fa-user-friends text-gray-600 mr-3"></i> Detalles de la Póliza
        </h5>
        <div class="info-grid text-left">
            <div>
                <h5><strong>¿Cubre Maternidad?:</strong> <?= formatBooleanIcon($model->cobertura_maternidad) ?></h5>
                <h5><strong>Deducible de Maternidad:</strong> <?= Yii::$app->formatter->asCurrency($model->deducible_maternidad ?? 0, 'USD') ?></h5>
                <h5><strong>Límite de Cobertura de Maternidad:</strong> <?= Yii::$app->formatter->asCurrency($model->limite_cobertura_maternidad ?? 0, 'USD') ?></h5>
            </div>
            <div>
                <h5><strong>Miembros del Grupo Familiar:</strong></h5>
                <pre class="p-2 bg-light rounded"><?= Html::encode($model->grupo_familiar ?? 'N/A') ?></pre>
            </div>
        </div>
    </div>
</div>
</div>