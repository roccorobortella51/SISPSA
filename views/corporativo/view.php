<?php


use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\Corporativo $model */

$this->title = 'DETALLES DEL AFILIADO CORPORATIVO: ' . Html::encode($model->nombre);
$this->params['breadcrumbs'][] = ['label' => 'Corporativos', 'url' => ['index']];
$this->params['breadcrumbs'][] = Html::encode($model->nombre);

\yii\web\YiiAsset::register($this);
$this->registerCssFile('https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css');
$this->registerJsFile('https://code.jquery.com/ui/1.13.2/jquery-ui.min.js', [
    'depends' => [\yii\web\JqueryAsset::class]
]);

/* Accesibilidad y legibilidad mejorada */
\Yii::$app->view->registerCssFile(Url::to('@web/css/corporativo-view-accessible.css'));

// Función auxiliar para formatear fechas, manejando valores nulos
function formatUpdatedAt($value) {
    if (empty($value)) {
        return 'No se ha modificado';
    }
    return Yii::$app->formatter->asDatetime($value, 'medium');
}

?>

<div class="main-container corporativo-view-large-font"> 

    <!-- Encabezado y Botones de Acción -->
    <div class="ms-panel-header">
        <h1 class="corporativo-view-h1 mb-3"><?= Html::encode($this->title) ?></h1>
        <div class="user-datos-btn-group">
            <?= Html::a(
                '<i class="fas fa-edit fa-lg me-3"></i> <span class="btn-label">Actualizar</span>',
                ['update', 'id' => $model->id],
                ['class' => 'btn btn-primary user-datos-btn']
            ) ?>
            <?= Html::a(
                '<i class="fas fa-file-contract fa-lg me-3"></i> <span class="btn-label">Ver Contratos</span>',
                ['contracts', 'id' => $model->id],
                ['class' => 'btn btn-info user-datos-btn']
            ) ?>
            <?= Html::a(
                '<i class="fas fa-undo fa-lg me-3"></i> <span class="btn-label">Volver</span>',
                Url::to(['index']),
                [
                    'class' => 'btn btn-secondary user-datos-btn',
                    'title' => 'Volver a la lista de corporativos',
                ]
            ) ?>
        </div>
    </div>

    <!-- Tarjeta de Información General del Corporativo -->
    <div class="ms-panel border-blue">
        <div class="ms-panel-body">
            <h3 class="section-title corporativo-view-section-title">
                <i class="fas fa-info-circle text-blue-600"></i> Información General del Corporativo
            </h3>
            <div class="info-grid">
                <div>
                    <p class="corporativo-view-content-text"><strong class="corporativo-view-label">Nombre:</strong> <span class="corporativo-view-content-text"><?= Html::encode($model->nombre) ?></span></p>
                    <p class="corporativo-view-content-text"><strong class="corporativo-view-label">Email:</strong> <?= Html::a(Html::encode($model->email), 'mailto:' . Html::encode($model->email), ['class' => 'text-blue-500 corporativo-view-content-text corporativo-view-content-text']) ?></p>
                    <p class="corporativo-view-content-text"><strong class="corporativo-view-label">Teléfono:</strong> <span class="corporativo-view-content-text"><?= Html::encode($model->telefono) ?></span></p>
                </div>
                <div>
                    <p class="corporativo-view-content-text"><strong class="corporativo-view-label">RIF:</strong> <span class="corporativo-view-content-text"><?= Html::encode($model->rif) ?></span></p>
                    <p class="corporativo-view-content-text"><strong class="corporativo-view-label">Estatus:</strong> <span class="status-badge <?= $model->estatus == 'Activo' ? 'active' : 'inactive' ?> corporativo-view-content-text"><?= Html::encode($model->estatus) ?></span></p>
                    <p class="corporativo-view-content-text"><strong class="corporativo-view-label">Código Asesor:</strong> <span class="corporativo-view-content-text"><?= Html::encode($model->codigo_asesor) ?></span></p>
                </div>
            </div>
            <div class="info-grid border-top">
                <div>
                    <p class="corporativo-view-content-text"><strong class="corporativo-view-label">Fecha de Creación:</strong> <span class="font-medium corporativo-view-content-text"><?= Html::encode(Yii::$app->formatter->asDatetime($model->created_at, 'medium')) ?></span></p>
                </div>
                <div>
                    <p class="corporativo-view-content-text"><strong class="corporativo-view-label">Última Actualización:</strong> <span class="font-medium corporativo-view-content-text"><?= Html::encode(formatUpdatedAt($model->updated_at)) ?></span></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Ubicación Geográfica -->
    <div class="ms-panel border-indigo">
        <div class="ms-panel-body">
            <h3 class="section-title corporativo-view-section-title">
                <i class="fas fa-map-marker-alt text-indigo-600"></i> Ubicación Geográfica
            </h3>
            <div class="info-grid">
                <div>
                    <p class="corporativo-view-content-text"><strong class="corporativo-view-label">Estado:</strong> <span class="corporativo-view-content-text"><?= Html::encode($model->rmEstado ? $model->rmEstado->nombre : $model->estado) ?></span></p>
                    <p class="corporativo-view-content-text"><strong class="corporativo-view-label">Municipio:</strong> <span class="corporativo-view-content-text"><?= Html::encode($model->rmMunicipio ? $model->rmMunicipio->nombre : $model->municipio) ?></span></p>
                </div>
                <div>
                    <p class="corporativo-view-content-text"><strong class="corporativo-view-label">Parroquia:</strong> <span class="corporativo-view-content-text"><?= Html::encode($model->rmParroquia ? $model->rmParroquia->nombre : $model->parroquia) ?></span></p>
                    <p class="corporativo-view-content-text"><strong class="corporativo-view-label">Ciudad:</strong> <span class="corporativo-view-content-text"><?= Html::encode($model->rmCiudad ? $model->rmCiudad->nombre : $model->ciudad) ?></span></p>
                </div>
            </div>
            <p class="border-top corporativo-view-content-text"><strong class="corporativo-view-label">Dirección:</strong> <span class="corporativo-view-content-text"><?= nl2br(Html::encode($model->direccion)) ?></span></p>
        </div>
    </div>

    <!-- Tarjeta de Información Registral -->
    <div class="ms-panel border-gray">
        <div class="ms-panel-body">
            <h3 class="section-title corporativo-view-section-title">
                <i class="fas fa-file-alt text-gray-600"></i> Información Registral
            </h3>
            <div class="info-grid">
                <div>
                    <p><strong>Lugar de Registro:</strong> <?= Html::encode($model->lugar_registro) ?></p>
                    <p><strong>Fecha Registro Mercantil:</strong> <span class="font-medium"><?= Html::encode(Yii::$app->formatter->asDate($model->fecha_registro_mercantil, 'long')) ?></span></p>
                </div>
                <div>
                    <p><strong>Tomo de Registro:</strong> <?= Html::encode($model->tomo_registro) ?></p>
                    <p><strong>Folio de Registro:</strong> <?= Html::encode($model->folio_registro) ?></p>
                </div>
            </div>
            <p class="border-top"><strong>Domicilio Fiscal:</strong> <?= nl2br(Html::encode($model->domicilio_fiscal)) ?></p>
        </div>
    </div>

    <!-- Tarjeta de Contacto Principal -->
    <div class="ms-panel border-yellow">
        <div class="ms-panel-body">
            <h3 class="section-title corporativo-view-section-title">
                <i class="fas fa-user-circle text-yellow-600"></i> Contacto Principal
            </h3>
            <div class="info-grid">
                <div>
                    <p><strong>Nombre Contacto:</strong> <?= Html::encode($model->contacto_nombre) ?></p>
                    <p><strong>Cédula Contacto:</strong> <?= Html::encode($model->contacto_cedula) ?></p>
                </div>
                <div>
                    <p><strong>Teléfono Contacto:</strong> <?= Html::encode($model->contacto_telefono) ?></p>
                    <p><strong>Cargo Contacto:</strong> <?= Html::encode($model->contacto_cargo) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Datos Financieros y de Actividad -->
    <div class="ms-panel border-green-500">
        <div class="ms-panel-body">
            <h3 class="section-title corporativo-view-section-title">
                <i class="fas fa-chart-line text-green-600"></i> Datos Financieros y de Actividad
            </h3>
            <div class="info-grid">
                <div>
                    <p><strong>Actividad Económica:</strong> <?= Html::encode($model->actividad_economica) ?></p>
                    <p><strong>Productos y Servicios:</strong> <?= Html::encode($model->productos_servicios) ?></p>
                </div>
                <div>
                    <p><strong>Utilidad Ejercicio Anterior:</strong> <?= Html::encode($model->utilidad_ejercicio_anterior) ?></p>
                    <p><strong>Patrimonio:</strong> <?= Html::encode($model->patrimonio) ?></p>
                </div>
            </div>
        </div>
    </div>

    <!-- Tarjeta de Datos del Representante Legal -->
    <div class="ms-panel border-blue-500">
        <div class="ms-panel-body">
            <h3 class="section-title corporativo-view-section-title">
                <i class="fas fa-user-tie text-blue-600"></i> Datos del Representante Legal
            </h3>
            <div class="info-grid">
                <div>
                    <p><strong>Nombre:</strong> <?= Html::encode($model->nombre_representante) ?></p>
                    <p><strong>Cédula:</strong> <?= Html::encode($model->cedula_representante) ?></p>
                    <p><strong>Nacionalidad:</strong> <?= Html::encode($model->nacionalidad_representante) ?></p>
                    <p><strong>Estado Civil:</strong> <?= Html::encode($model->estado_civil_representante) ?></p>
                </div>
                <div>
                    <p><strong>Lugar de Nacimiento:</strong> <?= Html::encode($model->lugar_nacimiento_representante) ?></p>
                    <p><strong>Fecha de Nacimiento:</strong> <?= Html::encode(Yii::$app->formatter->asDate($model->fecha_nacimiento_representante, 'long')) ?></p>
                    <p><strong>Sexo:</strong> <?= Html::encode($model->sexo_representante) ?></p>
                </div>
            </div>
            <div class="info-grid border-top">
                <div>
                    <p><strong>Profesión:</strong> <?= Html::encode($model->profesion_representante) ?></p>
                    <p><strong>Ocupación:</strong> <?= Html::encode($model->ocupacion_representante) ?></p>
                </div>
                <div>
                    <p><strong>Descripción de Actividad:</strong> <?= Html::encode($model->descripcion_actividad_representante) ?></p>
                </div>
            </div>
            <p class="border-top"><strong>Dirección:</strong> <?= nl2br(Html::encode($model->direccion_representante)) ?></p>
            <p><strong>Teléfono:</strong> <?= Html::encode($model->telefono_representante) ?></p>
        </div>
    </div>


    <!-- Secciones de Clínicas y Empleados Asociados -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Tarjeta de Clínicas Asociadas -->
        <div class="ms-panel border-green">
            <div class="ms-panel-body">
                <h3 class="section-title">
                    <i class="fas fa-hospital text-green-600"></i> Clínicas Asociadas
                </h3>
                <div class="mb-3">
                    <input id="typeahead-clinicas" type="text" class="form-control" placeholder="Buscar clínica por nombre o RIF...">
                </div>
                <?php if (!empty($model->clinicas)): ?>
                    <ul class="divide-y" id="clinicas-list">
                        <?php $clinicaIndex = 0; foreach ($model->clinicas as $clinica): ?>
                            <li class="py-3 flex justify-between items-center text-gray-700 clinica-item<?= $clinicaIndex >= 3 ? ' d-none' : '' ?>" data-nombre="<?= strtolower(Html::encode($clinica->nombre)) ?>" data-rif="<?= strtolower(Html::encode($clinica->rif)) ?>">
                                <span>
                                    <?= Html::a(Html::encode($clinica->nombre), ['rm-clinica/view', 'id' => $clinica->id], ['class' => 'text-blue-500 font-medium']) ?>
                                    <small class="text-gray-500 block sm:inline">(RIF: <?= Html::encode($clinica->rif) ?>)</small>
                                </span>
                                <i class="fas fa-arrow-right"></i>
                            </li>
                        <?php $clinicaIndex++; endforeach; ?>
                    </ul>
                    <?php if (count($model->clinicas) > 3): ?>
                        <div class="text-center mt-2">
                            <button id="ver-mas-clinicas" class="btn btn-link text-blue-600">Ver más</button>
                            <button id="ver-menos-clinicas" class="btn btn-link text-blue-600" style="display:none;">Ver menos</button>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="text-gray-500">No hay clínicas asociadas a este corporativo.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Tarjeta de Empleados Asociados -->
        <div class="ms-panel border-gray">
            <div class="ms-panel-body">
                <h3 class="section-title">
                    <i class="fas fa-user-tie text-gray-600"></i> Empleados Asociados
                </h3>
                <div class="mb-3">
                    <input id="typeahead-empleados" type="text" class="form-control" placeholder="Buscar empleado por nombre o usuario...">
                </div>
                <ul class="divide-y" id="empleados-list">
                    <?php $empleadoIndex = 0; foreach ($model->users as $user): ?>
                        <?php
                            $nombreCompleto = ($user->nombres ?? '') . ' ' . ($user->apellidos ?? '');
                            if (trim($nombreCompleto) === '') {
                                $nombreCompleto = $user->user->username ?? $user->userLogin->username ?? 'Sin nombre';
                            }
                            $usuarioLogin = $user->user->username ?? $user->userLogin->username ?? 'N/A';
                        ?>
                        <li class="py-3 flex justify-between items-center text-gray-700 empleado-item<?= $empleadoIndex >= 3 ? ' d-none' : '' ?>" data-nombre="<?= strtolower(Html::encode($nombreCompleto)) ?>" data-usuario="<?= strtolower(Html::encode($usuarioLogin)) ?>">
                            <span>
                                <?= Html::a(Html::encode($nombreCompleto), ['user-datos/view', 'id' => $user->id], ['class' => 'text-blue-500 font-medium']) ?>
                                <small class="text-gray-500 block sm:inline">(Usuario: <?= Html::encode($usuarioLogin) ?>)</small>
                            </span>
                            <i class="fas fa-arrow-right"></i>
                        </li>
                    <?php $empleadoIndex++; endforeach; ?>
                </ul>
                <?php if (count($model->users) > 3): ?>
                    <div class="text-center mt-2">
                        <button id="ver-mas-empleados" class="btn btn-link text-blue-600">Ver más</button>
                        <button id="ver-menos-empleados" class="btn btn-link text-blue-600" style="display:none;">Ver menos</button>
                    </div>
                <?php endif; ?>
                <?php if (empty($model->users)): ?>
                    <p class="text-gray-500">No hay empleados asociados a este corporativo.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php
$empleadosArray = [];
foreach ($model->users as $user) {
    $nombreCompleto = trim(($user->nombres ?? '') . ' ' . ($user->apellidos ?? ''));
    if ($nombreCompleto === '') {
        $nombreCompleto = $user->user->username ?? $user->userLogin->username ?? 'Sin nombre';
    }
    $usuarioLogin = $user->user->username ?? $user->userLogin->username ?? 'N/A';
    $empleadosArray[] = [
        'label' => $nombreCompleto . ' (' . $usuarioLogin . ')',
        'value' => $nombreCompleto,
        'id' => $user->id
    ];
}
$empleadosJson = json_encode($empleadosArray);

$clinicasArray = [];
foreach ($model->clinicas as $clinica) {
    $nombre = $clinica->nombre ?? '';
    $rif = $clinica->rif ?? '';
    $clinicasArray[] = [
        'label' => $nombre . ' (RIF: ' . $rif . ')',
        'value' => $nombre,
        'id' => $clinica->id
    ];
}
$clinicasJson = json_encode($clinicasArray);

$this->registerJs(<<<JS
$(function() {
    var empleados = $empleadosJson;
    $('#typeahead-empleados').autocomplete({
        minLength: 1,
        source: empleados,
        select: function(event, ui) {
            window.location.href = '/user-datos/view?id=' + ui.item.id;
        }
    });
    $('#typeahead-empleados').on('input', function() {
        var val = $(this).val().toLowerCase();
        $('.empleado-item').each(function() {
            var nombre = $(this).data('nombre');
            var usuario = $(this).data('usuario');
            if (nombre.indexOf(val) !== -1 || usuario.indexOf(val) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    var clinicas = $clinicasJson;
    $('#typeahead-clinicas').autocomplete({
        minLength: 1,
        source: clinicas,
        select: function(event, ui) {
            window.location.href = '/rm-clinica/view?id=' + ui.item.id;
        }
    });
    $('#typeahead-clinicas').on('input', function() {
        var val = $(this).val().toLowerCase();
        $('.clinica-item').each(function() {
            var nombre = $(this).data('nombre');
            var rif = $(this).data('rif');
            if (nombre.indexOf(val) !== -1 || rif.indexOf(val) !== -1) {
                $(this).show();
            } else {
                $(this).hide();
            }
        });
    });

    // Mostrar más clínicas
    $('#ver-mas-clinicas').on('click', function() {
        $('#clinicas-list .clinica-item.d-none').removeClass('d-none');
        $(this).hide();
        $('#ver-menos-clinicas').show();
    });
    // Mostrar menos clínicas
    $('#ver-menos-clinicas').on('click', function() {
        $('#clinicas-list .clinica-item').each(function(index) {
            if (index >= 3) {
                $(this).addClass('d-none');
            }
        });
        $(this).hide();
        $('#ver-mas-clinicas').show();
        // Scroll hacia la lista para mejor UX
        $('html, body').animate({ scrollTop: $('#clinicas-list').offset().top - 100 }, 300);
    });
    // Mostrar más empleados
    $('#ver-mas-empleados').on('click', function() {
        $('#empleados-list .empleado-item.d-none').removeClass('d-none');
        $(this).hide();
        $('#ver-menos-empleados').show();
    });
    // Mostrar menos empleados
    $('#ver-menos-empleados').on('click', function() {
        $('#empleados-list .empleado-item').each(function(index) {
            if (index >= 3) {
                $(this).addClass('d-none');
            }
        });
        $(this).hide();
        $('#ver-mas-empleados').show();
        // Scroll hacia la lista para mejor UX
        $('html, body').animate({ scrollTop: $('#empleados-list').offset().top - 100 }, 300);
    });
});
JS);
?>
<!-- Asegúrate de tener jQuery UI incluido en tu layout principal para que autocomplete funcione -->
