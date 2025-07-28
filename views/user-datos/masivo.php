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
?>
<style>


.file-input .file-toolbar{
    width: 150px !important;
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
</style>
<div class=row style="margin:3px !important;">
    <input type="hidden" id="csrf-token" value="<?= Yii::$app->request->csrfToken; ?>" />
    <div class="col-md-12 text-end">
        
    </div>
    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header d-flex justify-content-between align-items-center">
                <h1><?= $this->title ;?></h1>
            </div>
            <div class="ms-panel-body">
                <?php $form = ActiveForm::begin([
                    'id' => 'user-datos-form',
                    'options' => ['enctype' => 'multipart/form-data']
                ]); 
                ?>
                <div class="row">
                    <div class="col-md-12">
                        <?= $form->field($model, 'masivoFile')->widget(FileInput::classname(),[
                                'name' => 'attachments',
                                'pluginOptions' => [
                                    'browseClass' => 'btn btn-primary',
                                        'removeClass' => 'btn btn-secondary',
                                        'removeIcon' => '<i class="fas fa-trash"></i> ',
                                        'showUpload' => false,
                                        'showCancel' => false,
                                        'previewFileType' => 'image',
                                        'maxFileSize' => 2800,
                                        'previewSettings' => [
                                            'image' => ['width' => '150px', 'height' => 'auto'],
                                        ],
                                        //'initialPreview' => $initialPreview,
                                        //'initialPreviewAsData' => true,
                                        //'initialPreviewConfig' => $initialPreviewConfig,
                                        //'overwriteInitial' => true,
                                        //'layoutTemplates' => [
                                        //    'preview' => '<div class="file-preview {class}" style="width: 200px;"></div>',
                                        //],
                                    ],
                                    'options' => [
                                        //'disabled' => $disabled,
                                    ],
                                    ])->label('Foto del usuario');
                            ?>    
                    </div>
                </div>
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
                        <?= $form->field($modelContrato, 'plan_id')->widget(DepDrop::classname(), [ // <-- ¡VERIFICA EL MODELO!
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
                    <div class="col-md-4">
                        <?= $form->field($modelContrato, 'fecha_ini')->textInput([
                                        'class' => 'form-control form-control-lg',
                                        'type' => 'date',
                                        'placeholder' => 'Seleccione su fecha de nacimiento'
                                    ])->label('Fecha de Inicio') ?>
                    </div>
                    <div class="col-md-4">
                            <?= $form->field($modelContrato, 'fecha_ven')->textInput([
                                        'class' => 'form-control form-control-lg',
                                        'type' => 'date',
                                        'placeholder' => 'Seleccione su fecha de nacimiento'
                                    ])->label('Fecha de Vencimiento') ?>
                    </div>
                    <div class="col-md-4">
                        <?= $form->field($modelContrato, 'monto')->textInput(['class' => 'form-control  form-control-lg', 'type' => 'number']) ?>
                    </div>
                </div>
                <div class="form-group text-end mt-4"> <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg me-2']) ?> <?= Html::a('Cancelar', ['index', 'clinica_id' => $model->clinica_id], ['class' => 'btn btn-warning btn-lg']); ?>
                </div>
                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
    <div class="clearfix"></div>
</div>

