<?php

use yii\helpers\Html;
use kartik\form\ActiveForm;
use kartik\select2\Select2;
use yii\widgets\MaskedInput;
use app\components\UserHelper; 
use kartik\widgets\SwitchInput;
use yii\helpers\Url;
use yii\web\JsExpression;
use kartik\depdrop\DepDrop; 

// --- Calcula los IDs de los campos antes del bloque JS ---
$firstEmailFieldId = Html::getInputId($model, 'username');
$secondEmailFieldId = Html::getInputId($model, 'email');
$tEmailFieldId = Html::getInputId($model2, 'email');
// --- Fin de cálculo de IDs ---

// ID del campo de rol
$roleFieldId = Html::getInputId($model2, 'role');

$rol = UserHelper::getMyRol();
$permisos = ($rol == 'superadmin'); 
?>

<div class="user-form">
    <?php $form = ActiveForm::begin(['options' => ['class' => 'needs-validation']]); ?>

    <h3 class="mb-4">Datos de Usuario y Acceso</h3>
    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model, 'username')->label('NOMBRE DE USUARIO')->textInput([
                'maxlength' => true,
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Escriba una dirección de correo electronico válida',
                'autofocus' => true,
            ]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'password')->label('CONTRASEÑA')->passwordInput([
                'maxlength' => true,
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Ingrese una contraseña segura',
                'autofocus' => true,
            ]) ?>
        </div>

   
        <div class="col-md-4">
            <?= $form->field($model2, 'role')->label('ROL DEL USUARIO')->widget(Select2::classname(), [
                    'data' => UserHelper::getRolesAllRoles(), 
                    'options' => [
                        'placeholder' => 'Seleccione un rol...',
                        'class' => 'form-control form-control-lg',
                    ],
                    'pluginOptions' => [
                        'allowClear' => false,
                    ],
            ]);
            ?>
        </div>
        <div style="display:none">
           <?= $form->field($model, 'email')->label('CORREO ELECTRÓNICO (Usuario)')->textInput([
                'maxlength' => true,
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Ej: correo@ejemplo.com',
                'autofocus' => true,
                'readonly' => true
            ]) ?>
        </div>

        <?php if($permisos){?>
            <div class="col-md-4" id="clinica_field_container" style="display:none;">
                        <?= $form->field($model2, 'clinica_id')->widget(Select2::classname(), [
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
        <?php }?>
        <div class="col-md-4 d-flex align-items-center mt-3">
             <?= $form->field($model, 'status')->label('ESTATUS', ['class' => 'me-3'])->widget(SwitchInput::classname(), [
                'options' => [
                    'label' => false,
                ],
                'pluginOptions' => [
                    'onText' => 'Activo',
                    'offText' => 'Inactivo',
                    'onColor' => 'success',
                    'offColor' => 'danger',
                    'size' => 'large',
                    'onValue' => 1,
                    'offValue' => 0,
                ],
            ]); ?>
        </div>

        
    </div>
    
              

    <br>
    <hr>
    <h3 class="mb-4">Datos Personales del Usuario</h3>
    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model2, 'nombres')->textInput([ 
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Ingrese sus nombres completos',
            ])->label('NOMBRES') ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model2, 'apellidos')->textInput([ 
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Ingrese sus apellidos completos',
            ])->label('APELLIDOS') ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-2">
            <?= $form->field($model2, 'tipo_cedula')->widget(Select2::class, [
                'data' => ['V' => 'V', 'E' => 'E', 'J' => 'J', 'P' => 'P', 'N' => 'N', 'M' => 'M'],
                'options' => [
                    'placeholder' => 'Tipo',
                    'class' => 'form-control form-control-lg',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ])->label('TIPO ID')
            ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model2, 'cedula')->textInput([
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Ej: 12345678'
            ])->label('CÉDULA / RIF')
            ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model2, 'fechanac')->textInput([
                'class' => 'form-control form-control-lg',
                'type' => 'date',
                'placeholder' => 'DD/MM/AAAA'
            ])->label('FECHA NACIMIENTO') ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model2, 'sexo')->widget(Select2::class, [
                'data' => ['Masculino' => 'Masculino', 'Femenino' => 'Femenino'],
                'options' => [
                    'placeholder' => 'Seleccione sexo...',
                    'class' => 'form-control form-control-lg',
                ],
                'pluginOptions' => [
                    'allowClear' => true,
                ],
            ])->label('SEXO')
            ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4">
            <?= $form->field($model2, 'telefono')->textInput([
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Ej: 04121234567'
            ])->label('TELÉFONO') ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model2, 'email')->textInput([
                'class' => 'form-control form-control-lg',
                'type' => 'email',
                'placeholder' => 'Ej: personal@dominio.com',
                'readonly' => true 
            ])->label("CORREO ELECTRÓNICO") ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model2, 'codigoValidacion')->textInput([
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Código de validación (si aplica)'
            ])->label('CÓDIGO DE VALIDACIÓN') ?>
        </div>
    </div>

    <br>
    <hr>
    <h3 class="mb-4">Información de Ubicación</h3>
    <div class="row">
        <div class="col-md-3">
            <?= $form->field($model2, 'estado')->widget(Select2::classname(), [
                'data' => UserHelper::getEstadosList(),
                'options' => [
                    'placeholder' => 'Seleccione un estado...',
                    'class' => 'form-control  form-control-lg',
                    'id' => 'estado_id'
                ],
                'pluginOptions' => [
                    'allowClear' => false,
                ],
            ]);
            ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model2, 'municipio')->widget(DepDrop::classname(), [
                'type' => DepDrop::TYPE_SELECT2,
                'options' => [
                    'id' => 'municipio_id',
                    'placeholder' => 'Seleccione un municipio...',
                    'class' => 'form-control  form-control-lg',
                ],
                'pluginOptions' => [
                    'depends' => ['estado_id'],
                    'url' => Url::to(['/site/municipio']), 
                    'initialize' => true,
                ]
            ]);
            ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model2, 'parroquia')->widget(DepDrop::classname(), [
                'type' => DepDrop::TYPE_SELECT2,
                'options' => [
                    'id' => 'parroquia_id',
                    'placeholder' => 'Seleccione una parroquia...',
                    'class' => 'form-control  form-control-lg',
                ],
                'pluginOptions' => [
                    'depends' => ['municipio_id'],
                    'url' => Url::to(['/site/parroquia']), 
                    // 'initValueText' => isset($parroquiaName) ? $parroquiaName : '',
                ]
            ]);
            ?>
        </div>
        <div class="col-md-3">
            <?= $form->field($model2, 'ciudad')->widget(DepDrop::classname(), [
                'type' => DepDrop::TYPE_SELECT2,
                'options' => [
                    'id' => 'ciudad_id',
                    'placeholder' => 'Seleccione una ciudad...',
                    'class' => 'form-control  form-control-lg',
                ],
                'pluginOptions' => [
                    'depends' => ['estado_id'], 
                    'url' => Url::to(['/site/ciudad']), 
                    'initialize' => true,
                ]
            ]);  ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <?= $form->field($model2, 'direccion')->textInput([
                'class' => 'form-control form-control-lg',
                'placeholder' => 'Ej: Calle Principal, Edificio A, Apt. 12',
            ])->label('DIRECCIÓN COMPLETA') ?>
        </div>
    </div>

    <div class="form-group text-end mt-4">
        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar Usuario', ['class' => 'btn btn-success btn-lg']); ?>

        <?= Html::a('<i class="fas fa-undo"></i> Volver', ['index'], ['class' => 'btn btn-primary btn-lg']); ?>

        <?php if ($model->isNewRecord) { echo Html::a('Limpiar', ['create'], ['class' => 'btn btn-lg btn-outline-dark']); } ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php

$js = <<<JS
// Obtener los elementos jQuery usando los IDs calculados
var \$firstEmailField = $('#{$firstEmailFieldId}');
var \$secondEmailField = $('#{$secondEmailFieldId}');
var \$tEmailField = $('#{$tEmailFieldId}');

// Elementos para mostrar/ocultar clínica según el rol
var \$roleField = $('#{$roleFieldId}');
var \$clinicaContainer = $('#clinica_field_container');
var \$clinicaSelect = $('#clinica_id');

// Función para convertir a minúsculas y copiar el valor
function processAndCopyEmail() {
    // Convertir a minúsculas
    var emailValue = \$firstEmailField.val().toLowerCase();
    \$firstEmailField.val(emailValue);
    
    // Copiar a los otros campos
    if (\$firstEmailField.length && \$secondEmailField.length) {
        \$secondEmailField.val(emailValue);
        \$tEmailField.val(emailValue);
    }
}

// Escuchar eventos en el primer campo de email
\$firstEmailField.on('input', function() {
    processAndCopyEmail();
});

// Escuchar evento de cambio (cuando pierde el foco)
\$firstEmailField.on('change', function() {
    processAndCopyEmail();
});

// Escuchar evento de teclado para convertir en tiempo real
\$firstEmailField.on('keyup', function(e) {
    // No procesar teclas de navegación
    if (e.key.length === 1 || e.key === 'Backspace' || e.key === 'Delete') {
        var currentValue = \$(this).val();
        var cursorPosition = this.selectionStart;
        
        // Convertir a minúsculas
        var lowerValue = currentValue.toLowerCase();
        \$(this).val(lowerValue);
        
        // Mantener la posición del cursor
        this.setSelectionRange(cursorPosition, cursorPosition);
        
        // Copiar a los otros campos
        \$secondEmailField.val(lowerValue);
        \$tEmailField.val(lowerValue);
    }
});

// Opcional: Si el primer campo ya tiene un valor al cargar la página
if (\$firstEmailField.val() !== '') {
    processAndCopyEmail();
}

// --- Mostrar/Ocultar campo Clínica según rol seleccionado ---
function toggleClinicaByRole() {
    if (!\$roleField.length || !\$clinicaContainer.length) return;
    var val = \$roleField.val();
    var text = \$roleField.find('option:selected').text();
    var allowed = [
        'COORDINADOR-CLINICA',
        'ADMISION',
        'CONTROL DE CITAS',
        'ATENCIÓN',
        'Administrador-clinica',
        'afiliado',
        'Afiliado corporativo'
    ];
    var match = (val && allowed.indexOf(val) !== -1) || (text && allowed.indexOf(text) !== -1);
    if (match) {
        \$clinicaContainer.show();
    } else {
        \$clinicaContainer.hide();
        if (\$clinicaSelect.length) {
            \$clinicaSelect.val(null).trigger('change');
        }
    }
}

if (\$roleField.length) {
    // Escucha cambios (Select2 dispara 'change' sobre el select base)
    \$roleField.on('change', toggleClinicaByRole);
    // Evaluar estado inicial (en edición o al cargar)
    toggleClinicaByRole();
}
JS;
$this->registerJs($js);
?>