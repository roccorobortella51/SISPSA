<?php

use yii\helpers\Html;
use kartik\form\ActiveForm; // Asegúrate de que esto es 'kartik\form\ActiveForm'
use kartik\select2\Select2; // Para los selectores de estado y estatus
use yii\widgets\MaskedInput; // <--- ¡IMPORTANTE! Sigue siendo 'yii\widgets\MaskedInput' para el campo de cédula
use app\components\UserHelper;
use kartik\widgets\SwitchInput; // No usado en este fragmento, pero puede mantenerse.
use kartik\widgets\DatePicker;
use kartik\depdrop\DepDrop;
use yii\helpers\Url;
use kartik\widgets\FileInput;

use app\models\UserDatosType;

$currentRoute = Yii::$app->controller->getRoute(); // 'controlador/accion'


/** @var yii\web\View $this */
/** @var app\models\UserDatos $model */
/** @var app\models\Contratos $modelContrato // Asumo que tienes un modelo de Contrato separado para los datos de contrato */
/** @var yii\widgets\ActiveForm $form */


// --- BREADCRUMBS ---

$this->params['breadcrumbs'][] = ['label' => 'Afiliados', 'url' => ['index']];
// --- FIN  --- 


$this->title = 'Gestión Masiva de Afiliados'; 

$getPlanMontoUrl = Url::to(['/site/planmonto']);
$js = <<<JS
$('#plan_id').on('change', function() {
    var selectedPlanId = $(this).val(); // Obtiene el ID del plan seleccionado
    console.log("Plan ID seleccionado:", selectedPlanId);

    if (selectedPlanId) {
        $.ajax({
            url: '{$getPlanMontoUrl}', // Usa la URL generada por Yii
            type: 'GET',
            data: { id: selectedPlanId }, // Envía el ID del plan
            dataType: 'json', // Espera una respuesta JSON
            success: function(response) {
                if (response && typeof response.monto !== 'undefined') {
                    console.log("Monto del plan recibido:", response.monto);
                    // CAMBIO AQUÍ: Asegúrate que el ID del campo de monto en el formulario sea correcto
                    $('#contratos-monto').val(response.monto);
                } else {
                    console.log("Respuesta AJAX no válida o monto no encontrado.");
                    $('#contratos-monto').val(0); // O un valor por defecto
                }
            },
            error: function(xhr, status, error) {
                console.error("Error al obtener el monto del plan:", error);
                $('#contratos-monto').val(0); // En caso de error, limpia el campo
            }
        });
    } else {
        // Si no hay plan seleccionado (ej. se limpia el campo), el monto es 0
        $('#contratos-monto').val(0);
    }
});
JS;
$this->registerJs($js);

// JavaScript para mejorar la experiencia del usuario con los reportes de validación
$validationJs = <<<JS
$(document).ready(function() {
    // Agregar botón de cerrar a los reportes de validación
    $('.validation-report').each(function() {
        var closeBtn = $('<button type="button" class="btn-close" aria-label="Cerrar" style="position: absolute; top: 10px; right: 10px; background: none; border: none; font-size: 20px; color: #dc3545; cursor: pointer;">&times;</button>');
        $(this).css('position', 'relative').prepend(closeBtn);
        
        closeBtn.on('click', function() {
            $(this).parent().fadeOut();
        });
    });
    
    // Auto-scroll a los errores si existen
    if ($('.validation-report').length > 0) {
        $('html, body').animate({
            scrollTop: $('.validation-report').offset().top - 100
        }, 500);
    }
    
    // Resaltar filas con errores en la tabla
    $('.validation-report table tbody tr').hover(
        function() {
            $(this).addClass('table-warning');
        },
        function() {
            $(this).removeClass('table-warning');
        }
    );
});
JS;
$this->registerJs($validationJs);
?>
<style>
.file-input .file-toolbar{
    width: 250px !important;
    margin: 0 auto;
    box-sizing: border-box;
}

.file-input .file-preview {
    margin: 0 auto;
    box-sizing: border-box;
}

.file-input .file-caption {
    width: 150px !important;
    box-sizing: border-box;
}

/* Estilos para el reporte de validación */
.validation-report {
    background: #fff;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.validation-report h3 {
    color: #dc3545;
    margin-bottom: 15px;
    border-bottom: 2px solid #dc3545;
    padding-bottom: 10px;
}

.validation-report .summary {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
}

.validation-report .summary ul {
    margin-bottom: 0;
    padding-left: 20px;
}

.validation-report .summary li {
    margin-bottom: 5px;
}

.validation-report .errors h4 {
    color: #dc3545;
    margin-bottom: 15px;
}

.validation-report table {
    font-size: 14px;
}

.validation-report table th {
    background: #dc3545;
    color: white;
    font-weight: bold;
}

.validation-report table td {
    vertical-align: top;
}

.validation-report table td ul {
    margin-bottom: 0;
    padding-left: 15px;
}

.validation-report table td small {
    font-size: 12px;
    color: #666;
    line-height: 1.4;
}

/* Botón para descargar plantilla */
.download-template-btn {
    margin-bottom: 20px;
}

/* Responsive para tablas */
@media (max-width: 768px) {
    .validation-report table {
        font-size: 12px;
    }
    
    .validation-report table td small {
        font-size: 10px;
    }
}

/* Estilos para el botón de cerrar */
.validation-report .btn-close {
    transition: all 0.3s ease;
}

.validation-report .btn-close:hover {
    transform: scale(1.2);
    color: #000 !important;
}

/* Animación para el reporte */
.validation-report {
    animation: slideInDown 0.5s ease-out;
}

@keyframes slideInDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>
<div class=row style="margin:3px !important;">
    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    <div class="col-md-12 text-end">
        
    </div>
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header d-flex justify-content-between align-items-center">
                <h1><?= $this->title ;?></h1>
                <div class="d-flex" style="gap: 20px !important;">
                    <?= Html::a(
                        '<i class="fas fa-download"></i> Descargar Plantilla',
                        ['download-template'],
                        [
                            'class' => 'btn btn-outline-success btn-fixed-success',
                            'style' => 'font-size: 1.1rem !important; font-weight: bold !important; padding: 12px 17px !important; border-width: 3px !important; display: flex !important; align-items: center !important; gap: 12px !important; line-height: 1.1 !important;',
                            'title' => 'Descargar plantilla Excel de ejemplo'
                        ]
                    ) ?>
                    <!--Html::a(
                        '<i class="fas fa-download"></i> Descargar IDs de Ubicación',
                        ['download-location-ids'],
                        [
                            'class' => 'btn btn-outline-primary btn-fixed-primary',
                            'style' => 'font-size: 1.1rem !important; font-weight: bold !important; padding: 12px 17px !important; border-width: 3px !important; display: flex !important; align-items: center !important; gap: 12px !important; line-height: 1.1 !important;',
                            'title' => 'Descargar Excel con IDs de estados, municipios, parroquias y ciudades'
                        ]
                    )-->
                </div>
            </div>
            <div class="ms-panel-body">
                <div class="alert mb-4" style="background: #e0f7fa; color: #166534; border-radius: 12px; border: none; box-shadow: 0 2px 8px rgba(22,101,52,0.07);">
                    <h5><i class="fas fa-question-circle"></i> Ayuda para IDs de Ubicación</h5>
                    <p>Utiliza los siguientes selectores para consultar los <b>IDs</b> de Estado, Municipio, Parroquia y Ciudad que debes colocar en la plantilla Excel de carga masiva. Selecciona un Estado para ver sus Municipios, luego un Municipio para ver sus Parroquias, y así sucesivamente. El número que aparece junto al nombre es el <b>ID</b> que debes usar.</p>
                    <div class="row">
<?php
$estadosList = \app\components\UserHelper::getEstadosList();
foreach ($estadosList as $id => $nombre) {
    $estadosList[$id] = $nombre . ' (ID: ' . $id . ')';
}
?>
<div class="col-md-3">
    <?= \kartik\select2\Select2::widget([
        'name' => 'estado_id_help',
        'data' => $estadosList,
        'options' => [
            'id' => 'estado_id_help',
            'placeholder' => 'Seleccione Estado',
            'class' => 'form-control form-control-lg',
        ],
        'pluginOptions' => [
            'allowClear' => true,
        ],
    ]) ?>
</div>
<div class="col-md-3">
    <?= \kartik\depdrop\DepDrop::widget([
        'name' => 'municipio_id_help',
        'type' => \kartik\depdrop\DepDrop::TYPE_SELECT2,
        'options' => [
            'id' => 'municipio_id_help',
            'placeholder' => 'Seleccione Municipio',
            'class' => 'form-control form-control-lg',
        ],
        'pluginOptions' => [
            'depends' => ['estado_id_help'],
            'url' => \yii\helpers\Url::to(['/site/municipio-ids']),
            'initialize' => false,
        ],
    ]) ?>
</div>
<div class="col-md-3">
    <?= \kartik\depdrop\DepDrop::widget([
        'name' => 'parroquia_id_help',
        'type' => \kartik\depdrop\DepDrop::TYPE_SELECT2,
        'options' => [
            'id' => 'parroquia_id_help',
            'placeholder' => 'Seleccione Parroquia',
            'class' => 'form-control form-control-lg',
        ],
        'pluginOptions' => [
            'depends' => ['municipio_id_help'],
            'url' => \yii\helpers\Url::to(['/site/parroquia-ids']),
            'initialize' => false,
        ],
    ]) ?>
</div>
<div class="col-md-3">
    <?= \kartik\depdrop\DepDrop::widget([
        'name' => 'ciudad_id_help',
        'type' => \kartik\depdrop\DepDrop::TYPE_SELECT2,
        'options' => [
            'id' => 'ciudad_id_help',
            'placeholder' => 'Seleccione Ciudad',
            'class' => 'form-control form-control-lg',
        ],
        'pluginOptions' => [
            'depends' => ['estado_id_help'],
            'url' => \yii\helpers\Url::to(['/site/ciudad-ids']),
            'initialize' => false,
        ],
    ]) ?>
</div>
                    </div>
                    <small class="text-muted">El valor que aparece entre paréntesis o junto al nombre es el <b>ID</b> que debes colocar en la plantilla Excel.</small>
                </div>
                <?php
                // Mostrar reporte de validación si existe
                if (Yii::$app->session->hasFlash('error')) {
                    $flashMessage = Yii::$app->session->getFlash('error');
                    // Si el mensaje contiene HTML (es un reporte de validación)
                    if (strpos($flashMessage, '<div class="validation-report">') !== false) {
                        echo '<div class="alert alert-danger">';
                        echo $flashMessage; // Mostrar el HTML del reporte
                        echo '</div>';
                    } else {
                        // Mensaje de error normal
                        echo '<div class="alert alert-danger">' . Html::encode($flashMessage) . '</div>';
                    }
                }
                
                // Mostrar mensajes de éxito
                if (Yii::$app->session->hasFlash('success')) {
                    echo '<div class="alert alert-success">' . Html::encode(Yii::$app->session->getFlash('success')) . '</div>';
                }
                
                // Mostrar mensajes de advertencia
                if (Yii::$app->session->hasFlash('warning')) {
                    echo '<div class="alert alert-warning">' . Html::encode(Yii::$app->session->getFlash('warning')) . '</div>';
                }
                ?>
                
                <?php $form = ActiveForm::begin([
                    'id' => 'user-datos-form',
                    'options' => ['enctype' => 'multipart/form-data']
                ]); 
                ?>
                <h1>Datos del Contrato</h1>
                <div class = 'row'>
                    <div class="col-md-6">
                        <?= $form->field($model, 'clinica_id')->widget(Select2::classname(), [
                                'data' => UserHelper::getClinicasList(),
                                'options' => [
                                    'placeholder' => 'Seleccione',
                                    'class' => 'form-control  form-control-lg',
                                    'id' => 'clinica_id'
                                ],
                                'pluginOptions' => [
                                    'allowClear' => false,
                                ],
                            ])->label('Clinica'); ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($modelContrato, 'plan_id')->widget(DepDrop::classname(), [
                            'type' => DepDrop::TYPE_SELECT2,
                            'options'=>[
                                'id'=>'plan_id',
                                'placeholder' => 'Seleccione',
                                'class' => 'form-control  form-control-lg',
                            ],
                            'pluginOptions'=>[
                                'depends'=>['clinica_id'],
                                'url'=>Url::to(['/site/planes']),
                                'initialize' => true,
                                ]
                            ])->label('Plan');
                            ?>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($modelContrato, 'fecha_ini')->textInput([
                            'class' => 'form-control form-control-lg fecha-ini-field',
                            'type' => 'date',
                            'required' => true,
                        ])->label('<i class="fas fa-calendar-alt me-2"></i> Fecha de Inicio de contratos', [
                            'class' => 'fw-bold text-dark mb-2',
                            'encode' => false
                        ]) ?>
                    </div>
                    <div class="col-md-6 fecha-ven-container" style="display: none;">
                        <?= $form->field($modelContrato, 'fecha_ven')->textInput([
                            'class' => 'form-control form-control-lg fecha-ven-field',
                            'type' => 'date',
                            'required' => true,
                        ])->label('<i class="fas fa-calendar-check me-2"></i> Fecha de Vencimiento de contratos', [
                            'class' => 'fw-bold text-dark mb-2',
                            'encode' => false
                        ]) ?>
                    </div>
                </div>
                <?php
                $this->registerJs(<<<JS
                function calcularFechaVencimiento(fechaIni) {
                    if (fechaIni) {
                        var parts = fechaIni.split('-');
                        var year = parseInt(parts[0]);
                        var month = parseInt(parts[1]) - 1;
                        var day = parseInt(parts[2]);
                        var fecha = new Date(year, month, day);
                        fecha.setFullYear(fecha.getFullYear() + 1);
                        var newYear = fecha.getFullYear();
                        var newMonth = String(fecha.getMonth() + 1).padStart(2, '0');
                        var newDay = String(fecha.getDate()).padStart(2, '0');
                        return newYear + '-' + newMonth + '-' + newDay;
                    }
                    return '';
                }
                function toggleFechaVen() {
                    var fechaIni = $('.fecha-ini-field').val();
                    var fechaVenContainer = $('.fecha-ven-container');
                    if (fechaIni) {
                        fechaVenContainer.show();
                        var fechaVen = calcularFechaVencimiento(fechaIni);
                        $('.fecha-ven-field').val(fechaVen);
                    } else {
                        fechaVenContainer.hide();
                        $('.fecha-ven-field').val('');
                    }
                }
                $(function() {
                    toggleFechaVen();
                    $('.fecha-ini-field').on('change', function() {
                        toggleFechaVen();
                    });
                });
                JS
                );
                ?>
                <div class  = "row">
                    <div class="col-md-12">
                        <h1>Archivo de Datos del Afiliado</h1>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <div class="alert alert-info">
                            <h5><i class="fas fa-info-circle"></i> Información sobre el archivo Excel</h5>
                            <p><strong>Formato requerido:</strong> El archivo debe tener las siguientes columnas:</p>
                            <ul>
                                <li><strong>A:</strong> Email (obligatorio, formato válido)</li>
                                <li><strong>B:</strong> Teléfono (obligatorio, formato venezolano)</li>
                                <li><strong>C:</strong> Nombres (obligatorio, mínimo 2 caracteres)</li>
                                <li><strong>D:</strong> Apellidos (obligatorio, mínimo 2 caracteres)</li>
                                <li><strong>E:</strong> Tipo Cédula (obligatorio: V, E, P, J)</li>
                                <li><strong>F:</strong> Cédula (obligatorio, 6-10 dígitos numéricos)</li>
                                <li><strong>G:</strong> Fecha Nacimiento (obligatorio, formato DD/MM/YYYY)</li>
                                <li><strong>H:</strong> Sexo (obligatorio: M, F, Masculino, Femenino)</li>
                                <li><strong>I:</strong> Tipo Sangre (opcional: A+, A-, B+, B-, AB+, AB-, O+, O-)</li>
                                <li><strong>J:</strong> Estado ID (opcional, ID numérico)</li>
                                <li><strong>K:</strong> Municipio ID (opcional, ID numérico)</li>
                                <li><strong>L:</strong> Parroquia ID (opcional, ID numérico)</li>
                                <li><strong>M:</strong> Ciudad ID (opcional, ID numérico)</li>
                                <li><strong>N:</strong> Dirección (obligatorio, mínimo 10 caracteres)</li>
                            </ul>
                            <p><strong>Nota:</strong> La primera fila debe contener los encabezados. Descarga la plantilla de ejemplo para ver el formato correcto.</p>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?= $form->field($model, 'masivoFile')->widget(FileInput::classname(),[
                                'name' => 'attachments',
                                'pluginOptions' => [
                                    'browseClass' => 'btn btn-lg',
                                    'browseIcon' => '<i class="fas fa-folder-open"></i> ',
                                    'browseLabel' => 'Examinar archivo',
                                    'browseStyle' => 'background: #b9fbc0; color: #166534; font-weight: bold; border-radius: 10px; box-shadow: 0 2px 12px rgba(22,101,52,0.12); padding: 22px 90px; border: none; font-size: 1.22rem; letter-spacing: 0.5px; min-width: 480px; max-width: 100%;',
                                    'removeClass' => 'btn btn-lg w-100',
                                    'removeIcon' => '<i class="fas fa-trash"></i> ',
                                    'removeLabel' => 'Quitar',
                                    'removeStyle' => 'background: #ffe5e5; color: #b91c1c; font-weight: bold; border-radius: 10px; box-shadow: 0 2px 8px rgba(185,28,28,0.08); padding: 16px 32px; border: none; font-size: 1.15rem; letter-spacing: 0.5px;',
                                    'showUpload' => false,
                                    'showCancel' => false,
                                    'previewFileType' => 'image',
                                    'maxFileSize' => 2800,
                                    'previewSettings' => [
                                        'image' => ['width' => '150px', 'height' => 'auto'],
                                    ],
                                ],
                                'options' => [
                                    //'disabled' => $disabled,
                                ],
                            ])->label(false);
                        ?>    
                    </div>
                </div>
                <div class="form-group mt-4 d-flex justify-content-center" style="gap: 20px !important;">
                    <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', [
                        'class' => 'btn btn-outline-success btn-fixed-success',
                        'style' => 'font-size: 1.1rem !important; font-weight: bold !important; padding: 12px 17px !important; border-width: 3px !important; display: flex !important; align-items: center !important; gap: 12px !important; line-height: 1.1 !important;'
                    ]) ?>
                    <?= Html::a('<i class="fas fa-times"></i> Cancelar', ['index', 'clinica_id' => $model->clinica_id], [
                        'class' => 'btn btn-danger',
                        'style' => 'font-size: 1.1rem !important; font-weight: bold !important; padding: 12px 17px !important; border-width: 3px !important; display: flex !important; align-items: center !important; gap: 12px !important; line-height: 1.1 !important;'
                    ]) ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>

