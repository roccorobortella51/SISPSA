<?php
// views/profile/update.php - WITH PROPER DROPDOWNS

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use kartik\depdrop\DepDrop;
use kartik\select2\Select2;
use app\models\RmEstado;
use app\models\RmCiudad;
use app\models\RmMunicipio;
use app\models\RmParroquia;

$this->title = 'Editar Perfil';
$this->params['breadcrumbs'][] = ['label' => 'Mi Perfil', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Get estados list for dropdown (using 'codigo' as value)
$estadosList = RmEstado::find()->select(['codigo', 'nombre'])->orderBy('nombre')->all();
$estadosData = \yii\helpers\ArrayHelper::map($estadosList, 'codigo', 'nombre');

// Current photo URL with cache buster
$currentPhotoUrl = !empty($userDatos->selfie) ? Yii::getAlias('@web/' . $userDatos->selfie) . '?v=' . time() : null;

// Get current values for DepDrop initialization
$currentEstado = $userDatos->estado;
$currentMunicipio = $userDatos->municipio;
$currentParroquia = $userDatos->parroquia;
$currentCiudad = $userDatos->ciudad;
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header" style="background: linear-gradient(135deg, #0F6CBD 0%, #106EBE 100%);">
                    <h3 class="card-title" style="color: white;">
                        <i class="fas fa-user-edit"></i> Editar Información Personal
                    </h3>
                </div>

                <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($userDatos, 'nombres')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($userDatos, 'apellidos')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <?= $form->field($userDatos, 'tipo_cedula')->dropDownList([
                                '' => 'Seleccione tipo de cédula',
                                'V' => 'Venezolano (V)',
                                'E' => 'Extranjero (E)',
                                'J' => 'Jurídico (J)',
                                'G' => 'Gobierno (G)',
                                'Menor Sin Cédula' => 'Menor Sin Cédula',
                            ]) ?>
                        </div>
                        <div class="col-md-4">
                            <?= $form->field($userDatos, 'cedula')->textInput(['type' => 'number']) ?>
                        </div>
                        <div class="col-md-4" id="consecutivo-container" style="display: none;">
                            <?= $form->field($userDatos, 'consecutivo_menor')->textInput(['type' => 'number', 'min' => 1, 'max' => 20]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($userDatos, 'fechanac')->input('date') ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($userDatos, 'sexo')->dropDownList([
                                '' => 'Seleccione sexo',
                                'Masculino' => 'Masculino',
                                'Femenino' => 'Femenino',
                                'Otro' => 'Otro',
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($userDatos, 'email')->textInput(['maxlength' => true]) ?>
                        </div>
                        <div class="col-md-6">
                            <?= $form->field($userDatos, 'telefono')->textInput(['maxlength' => true]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <?= $form->field($userDatos, 'direccion')->textarea(['rows' => 3]) ?>
                        </div>
                    </div>

                    <!-- CASCADING LOCATION DROPDOWNS -->
                    <div class="row">
                        <div class="col-md-3">
                            <?= $form->field($userDatos, 'estado')->widget(Select2::class, [
                                'data' => $estadosData,
                                'options' => [
                                    'placeholder' => 'Seleccione un estado',
                                    'id' => 'estado-select',
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                ],
                            ]) ?>
                        </div>

                        <div class="col-md-3">
                            <?= $form->field($userDatos, 'municipio')->widget(DepDrop::class, [
                                'type' => DepDrop::TYPE_SELECT2,
                                'options' => [
                                    'id' => 'municipio-select',
                                    'placeholder' => 'Seleccione un municipio',
                                ],
                                'select2Options' => [
                                    'pluginOptions' => ['allowClear' => true],
                                ],
                                'pluginOptions' => [
                                    'depends' => ['estado-select'],
                                    'url' => Url::to(['/site/municipio']),
                                    'loadingText' => 'Cargando municipios...',
                                    'initDepends' => ['estado-select'],
                                    'initialize' => true,
                                ],
                            ]) ?>
                        </div>

                        <div class="col-md-3">
                            <?= $form->field($userDatos, 'parroquia')->widget(DepDrop::class, [
                                'type' => DepDrop::TYPE_SELECT2,
                                'options' => [
                                    'id' => 'parroquia-select',
                                    'placeholder' => 'Seleccione una parroquia',
                                ],
                                'select2Options' => [
                                    'pluginOptions' => ['allowClear' => true],
                                ],
                                'pluginOptions' => [
                                    'depends' => ['municipio-select'],
                                    'url' => Url::to(['/site/parroquia']),
                                    'loadingText' => 'Cargando parroquias...',
                                    'initDepends' => ['municipio-select'],
                                    'initialize' => true,
                                ],
                            ]) ?>
                        </div>

                        <div class="col-md-3">
                            <?= $form->field($userDatos, 'ciudad')->widget(DepDrop::class, [
                                'type' => DepDrop::TYPE_SELECT2,
                                'options' => [
                                    'id' => 'ciudad-select',
                                    'placeholder' => 'Seleccione una ciudad',
                                ],
                                'select2Options' => [
                                    'pluginOptions' => ['allowClear' => true],
                                ],
                                'pluginOptions' => [
                                    'depends' => ['estado-select'],
                                    'url' => Url::to(['/site/ciudad']),
                                    'loadingText' => 'Cargando ciudades...',
                                    'initDepends' => ['estado-select'],
                                    'initialize' => true,
                                ],
                            ]) ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($userDatos, 'tipo_sangre')->dropDownList([
                                '' => 'Seleccione tipo de sangre',
                                'A+' => 'A+',
                                'A-' => 'A-',
                                'B+' => 'B+',
                                'B-' => 'B-',
                                'AB+' => 'AB+',
                                'AB-' => 'AB-',
                                'O+' => 'O+',
                                'O-' => 'O-',
                            ]) ?>
                        </div>
                        <div class="col-md-6">
                            <label>Foto de Perfil</label>
                            <input type="file" name="UserDatos[selfieFile]" accept="image/*" class="form-control" id="selfie-file-input">
                            <small class="text-muted">Formatos permitidos: JPG, PNG. Máximo 2MB.</small>

                            <!-- Current Photo Display -->
                            <div class="mt-3" id="current-photo-container">
                                <label>Foto actual:</label>
                                <div class="mt-2">
                                    <?php if ($currentPhotoUrl): ?>
                                        <img id="current-photo-img" src="<?= $currentPhotoUrl ?>"
                                            style="max-width: 100px; max-height: 100px; border-radius: 8px;">
                                    <?php else: ?>
                                        <div id="no-photo-message">
                                            <i class="fas fa-user-circle" style="font-size: 60px; color: #6c757d;"></i>
                                            <p class="text-muted mt-1">No hay foto actual</p>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- New Photo Preview -->
                            <div class="mt-3" id="new-photo-preview-container" style="display: none;">
                                <label>Nueva foto seleccionada:</label>
                                <div class="mt-2">
                                    <img id="new-photo-preview" style="max-width: 100px; max-height: 100px; border-radius: 8px;">
                                    <button type="button" class="btn btn-sm btn-danger ml-2" id="cancel-new-photo">
                                        <i class="fas fa-times"></i> Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card-footer">
                    <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Cambios', ['class' => 'btn btn-primary']) ?>
                    <?= Html::a('<i class="fas fa-times"></i> Cancelar', ['index'], ['class' => 'btn btn-default']) ?>
                </div>

                <?php ActiveForm::end(); ?>
            </div>
        </div>
    </div>
</div>

<?php
$script = <<< JS
// Set initial values for DepDrop
var currentEstado = '{$currentEstado}';
var currentMunicipio = '{$currentMunicipio}';
var currentParroquia = '{$currentParroquia}';
var currentCiudad = '{$currentCiudad}';

// Initialize DepDrop after page load
$(document).ready(function() {
    // Set estado value
    if (currentEstado) {
        $('#estado-select').val(currentEstado).trigger('change');
    }
    
    // Set municipio after it loads
    $('#municipio-select').on('depdrop:ready', function() {
        if (currentMunicipio) {
            $('#municipio-select').val(currentMunicipio).trigger('change');
        }
    });
    
    // Set parroquia after it loads
    $('#parroquia-select').on('depdrop:ready', function() {
        if (currentParroquia) {
            $('#parroquia-select').val(currentParroquia);
        }
    });
    
    // Set ciudad after it loads
    $('#ciudad-select').on('depdrop:ready', function() {
        if (currentCiudad) {
            $('#ciudad-select').val(currentCiudad);
        }
    });
});

// Show/hide consecutivo_menor based on tipo_cedula
function toggleConsecutivo() {
    var tipoCedula = $('#userdatos-tipo_cedula').val();
    var consecutivoContainer = $('#consecutivo-container');
    if (tipoCedula === 'Menor Sin Cédula') {
        consecutivoContainer.show();
    } else {
        consecutivoContainer.hide();
        $('#userdatos-consecutivo_menor').val('');
    }
}

// LIVE PHOTO PREVIEW
var fileInput = document.getElementById('selfie-file-input');
if (fileInput) {
    fileInput.addEventListener('change', function(e) {
        var file = e.target.files[0];
        var newPreviewContainer = document.getElementById('new-photo-preview-container');
        var newPreview = document.getElementById('new-photo-preview');
        var currentImg = document.getElementById('current-photo-img');
        var noPhotoMsg = document.getElementById('no-photo-message');
        
        if (file) {
            if (file.size > 2 * 1024 * 1024) {
                alert('El archivo es demasiado grande. Máximo 2MB.');
                this.value = '';
                return;
            }
            if (!file.type.match('image.*')) {
                alert('Solo se permiten archivos de imagen.');
                this.value = '';
                return;
            }
            
            var reader = new FileReader();
            reader.onload = function(event) {
                newPreview.src = event.target.result;
                newPreviewContainer.style.display = 'block';
                if (currentImg) currentImg.style.display = 'none';
                if (noPhotoMsg) noPhotoMsg.style.display = 'none';
            };
            reader.readAsDataURL(file);
        }
    });
}

// Cancel new photo selection
var cancelBtn = document.getElementById('cancel-new-photo');
if (cancelBtn) {
    cancelBtn.addEventListener('click', function() {
        var fileInputEl = document.getElementById('selfie-file-input');
        fileInputEl.value = '';
        document.getElementById('new-photo-preview-container').style.display = 'none';
        document.getElementById('new-photo-preview').src = '';
        
        var currentImg = document.getElementById('current-photo-img');
        var noPhotoMsg = document.getElementById('no-photo-message');
        
        if (currentImg && currentImg.src && currentImg.src !== window.location.href) {
            currentImg.style.display = 'block';
        } else if (noPhotoMsg) {
            noPhotoMsg.style.display = 'block';
        }
    });
}

toggleConsecutivo();
$('#userdatos-tipo_cedula').on('change', toggleConsecutivo);
JS;

$this->registerJs($script);
?>