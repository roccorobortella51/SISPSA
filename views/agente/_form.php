<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use app\components\UserHelper;
use yii\helpers\Url;
use yii\web\View;

$mode = $mode ?? 'create';
$isNewRecord = $isNewRecord ?? true;

// Calculate field IDs for JavaScript
$cedulaSearchId = 'cedula-search';
$propietarioFieldId = Html::getInputId($model, 'idusuariopropietario');
$userCreateSectionId = 'user-create-section';

// URL variables for AJAX calls - ABSOLUTE URLs
$findByCedulaUrl = Url::to(['/user/find-by-cedula'], true);
$createAgenteUserUrl = Url::to(['/user/create-agente-user'], true);

?>

<style>
/* No CSS hiding - we control visibility via JavaScript */
</style>

<div class="agente-form">
    <?php $form = ActiveForm::begin(['id' => 'agente-form']); ?>
    
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="m-0" style="font-size: 1.5rem !important;"><i class="fas fa-info-circle mr-2"></i> Información General de la Agencia</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <?= $form->field($model, 'nom')->label('NOMBRE DE LA AGENCIA', ['style' => 'font-size: 1.5rem !important; font-weight: bold;'])->textInput([
                                'maxlength' => true,
                                'class' => 'form-control',
                                'placeholder' => 'Nombre completo del agente',
                                'autofocus' => true,
                                'style' => 'font-size: 1.25rem !important; height: calc(2.5em + 1.25rem) !important;'
                            ]) ?>
                        </div>

                        <div class="col-md-6">
                            <?= $form->field($model, 'sudeaseg')->label('CÓDIGO SUDEASEG', ['style' => 'font-size: 1.5rem !important; font-weight: bold;'])->textInput([
                                'maxlength' => true,
                                'class' => 'form-control',
                                'placeholder' => 'Ingrese el código SUDEASEG',
                                'style' => 'font-size: 1.25rem !important; height: calc(2.5em + 1.25rem) !important;'
                            ]) ?>
                        </div>
                    </div>
                    
                    <!-- Search Field for Propietario by Cedula -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label style="font-size: 1.5rem !important; font-weight: bold;">BUSCAR PROPIETARIO POR CÉDULA</label>
                                <div class="input-group">
                                    <?= Html::textInput('cedula_search', '', [
                                        'id' => $cedulaSearchId,
                                        'class' => 'form-control',
                                        'placeholder' => 'Ingrese la cédula de identidad del propietario',
                                        'style' => 'font-size: 1.25rem !important; height: calc(2.5em + 1.25rem) !important;'
                                    ]) ?>
                                    <div class="input-group-append">
                                        <?= Html::button('<i class="fas fa-search"></i> Buscar', [
                                            'id' => 'search-propietario-btn',
                                            'class' => 'btn btn-primary',
                                            'style' => 'font-size: 1.25rem !important; height: calc(2.5em + 1.25rem) !important;'
                                        ]) ?>
                                    </div>
                                </div>
                                <div id="search-result" class="mt-2" style="display: none;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Existing Propietario Selection Field (Hidden initially) -->
                    <div class="row" id="propietario-selection-row">
                        <div class="col-md-12">
                            <?php
                            $agentesList = UserHelper::getAgentesList();
                            $select2Options = [
                                'data' => $agentesList,
                                'options' => [
                                    'placeholder' => 'Seleccione',
                                    'class' => 'form-control',
                                    'style' => 'font-size: 1.25rem !important;',
                                    'id' => $propietarioFieldId
                                ],
                                'pluginOptions' => [
                                    'allowClear' => false,
                                ],
                            ];
                            echo $form->field($model, 'idusuariopropietario')->label('NOMBRE DEL PROPIETARIO', ['style' => 'font-size: 1.5rem !important; font-weight: bold;'])->widget(Select2::classname(), $select2Options);
                            ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- User Creation Section (Hidden initially) -->
    <div id="<?= $userCreateSectionId ?>" class="row" style="display: none;">
        <div class="col-md-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-warning text-dark">
                    <h5 class="m-0" style="font-size: 1.5rem !important;">
                        <i class="fas fa-user-plus mr-2"></i> Crear Nuevo Propietario (No encontrado en el sistema)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info" style="font-size: 1.3rem !important; padding: 1.5rem !important;">
                        <i class="fas fa-info-circle mr-2"></i>
                        El propietario no fue encontrado en el sistema. Por favor, complete la información para crear un nuevo usuario con rol "Agente".
                    </div>
                    
                    <!-- User Creation Form Fields -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size: 1.25rem !important; font-weight: bold;">NOMBRES *</label>
                                <?= Html::textInput('new_user_nombres', '', [
                                    'class' => 'form-control',
                                    'placeholder' => 'Nombres del propietario',
                                    'style' => 'font-size: 1.1rem !important; height: calc(2.25em + 1rem) !important;',
                                    'required' => true
                                ]) ?>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label style="font-size: 1.25rem !important; font-weight: bold;">APELLIDOS *</label>
                                <?= Html::textInput('new_user_apellidos', '', [
                                    'class' => 'form-control',
                                    'placeholder' => 'Apellidos del propietario',
                                    'style' => 'font-size: 1.1rem !important; height: calc(2.25em + 1rem) !important;',
                                    'required' => true
                                ]) ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size: 1.25rem !important; font-weight: bold;">TIPO CÉDULA *</label>
                                <?= Html::dropDownList('new_user_tipo_cedula', 'V', 
                                    ['V' => 'V', 'E' => 'E', 'J' => 'J', 'P' => 'P', 'N' => 'N', 'M' => 'M'], [
                                    'class' => 'form-control',
                                    'style' => 'font-size: 1.1rem !important; height: calc(2.25em + 1rem) !important;',
                                    'required' => true
                                ]) ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size: 1.25rem !important; font-weight: bold;">CÉDULA *</label>
                                <?= Html::textInput('new_user_cedula', '', [
                                    'id' => 'new_user_cedula',
                                    'class' => 'form-control',
                                    'placeholder' => 'Número de cédula',
                                    'style' => 'font-size: 1.1rem !important; height: calc(2.25em + 1rem) !important;',
                                    'readonly' => true,
                                    'required' => true
                                ]) ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size: 1.25rem !important; font-weight: bold;">TELÉFONO</label>
                                <?= Html::textInput('new_user_telefono', '', [
                                    'class' => 'form-control',
                                    'placeholder' => 'Ej: 04121234567',
                                    'style' => 'font-size: 1.1rem !important; height: calc(2.25em + 1rem) !important;'
                                ]) ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label style="font-size: 1.25rem !important; font-weight: bold;">EMAIL *</label>
                                <?= Html::textInput('new_user_email', '', [
                                    'class' => 'form-control',
                                    'type' => 'email',
                                    'placeholder' => 'correo@ejemplo.com',
                                    'style' => 'font-size: 1.1rem !important; height: calc(2.25em + 1rem) !important;',
                                    'required' => true
                                ]) ?>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label style="font-size: 1.25rem !important; font-weight: bold;">CONTRASEÑA TEMPORAL *</label>
                                <?= Html::textInput('new_user_password', '', [
                                    'class' => 'form-control',
                                    'placeholder' => 'Contraseña temporal para el usuario',
                                    'style' => 'font-size: 1.1rem !important; height: calc(2.25em + 1rem) !important;',
                                    'required' => true
                                ]) ?>
                                <small class="form-text text-muted" style="font-size: 1rem !important;">El usuario podrá cambiar esta contraseña al iniciar sesión por primera vez.</small>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <?= Html::button('<i class="fas fa-user-check mr-2"></i> Crear Propietario y Asignar', [
                                'id' => 'create-propietario-btn',
                                'class' => 'btn btn-success btn-lg'
                            ]) ?>
                            <?= Html::button('<i class="fas fa-times mr-2"></i> Cancelar', [
                                'id' => 'cancel-create-btn',
                                'class' => 'btn btn-secondary btn-lg ml-2'
                            ]) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rest of the existing form (Porcentajes section) -->
    <div class="row">
        <div class="col-md-12">
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="m-0" style="font-size: 1.5rem !important;"></i> Porcentajes (%) de Comisiónes</h5>
                </div>
                <div class="card-body">
                    <div class="row g-2">
                        <div class="col-6 col-sm-4 col-md-2">
                            <?= $form->field($model, 'por_venta')->label('VENTA ' . Html::tag('i', '', [
                                'class' => 'fas fa-info-circle ml-1 text-info',
                                'data-toggle' => 'tooltip', 
                                'title' => 'Escriba el porcentaje de comisión para ventas.',
                                'data-placement' => 'top'
                            ]), [
                                'style' => 'font-size: 1.25rem !important; font-weight: bold;'
                            ])->textInput([
                                'class' => 'form-control',
                                'placeholder' => 'Ingrese el % por Venta',
                                'type' => 'number',
                                'step' => '0.01',
                                'style' => 'font-size: 1.1rem !important; height: calc(2.25em + 1rem) !important;'
                            ]) ?>
                        </div>
                        <div class="col-6 col-sm-4 col-md-2">
                            <?= $form->field($model, 'por_asesor')->label('ASESORÍA ' . Html::tag('i', '', [
                                'class' => 'fas fa-info-circle ml-1 text-info',
                                'data-toggle' => 'tooltip', 
                                'title' => 'Establece el porcentaje de comisión por servicios de asesoría.',
                                'data-placement' => 'top'
                            ]), [
                                'style' => 'font-size: 1.25rem !important; font-weight: bold;'
                            ])->textInput([
                                'class' => 'form-control',
                                'placeholder' => 'Ingrese el % por Asesoría',
                                'type' => 'number',
                                'step' => '0.01',
                                'style' => 'font-size: 1.1rem !important; height: calc(2.25em + 1rem) !important;'
                            ]) ?>
                        </div>
                        <div class="col-6 col-sm-4 col-md-2">
                            <?= $form->field($model, 'por_cobranza')->label('COBRANZA ' . Html::tag('i', '', [
                                'class' => 'fas fa-info-circle ml-1 text-info',
                                'data-toggle' => 'tooltip', 
                                'title' => 'Define la comisión para la gestión de cobranza.',
                                'data-placement' => 'top'
                            ]), [
                                'style' => 'font-size: 1.25rem !important; font-weight: bold;'
                            ])->textInput([
                                'class' => 'form-control',
                                'placeholder' => 'Ingrese el % por Cobranza',
                                'type' => 'number',
                                'step' => '0.01',
                                'style' => 'font-size: 1.1rem !important; height: calc(2.25em + 1rem) !important;'
                            ]) ?>
                        </div>

                        <div class="col-6 col-sm-4 col-md-2">
                            <?= $form->field($model, 'por_post_venta')->label('POST VENTA ' . Html::tag('i', '', [
                                'class' => 'fas fa-info-circle ml-1 text-info',
                                'data-toggle' => 'tooltip', 
                                'title' => 'Ingrese el porcentaje para servicios de post-venta.',
                                'data-placement' => 'top'
                            ]), [
                                'style' => 'font-size: 1.25rem !important; font-weight: bold;'
                            ])->textInput([
                                'class' => 'form-control',
                                'placeholder' => 'Ingrese el % por Post-Venta',
                                'type' => 'number',
                                'step' => '0.01',
                                'style' => 'font-size: 1.1rem !important; height: calc(2.25em + 1rem) !important;'
                            ]) ?>
                        </div>
                        <div class="col-6 col-sm-4 col-md-2">
                            <?= $form->field($model, 'por_agente')->label('AGENCIA ' . Html::tag('i', '', [
                                'class' => 'fas fa-info-circle ml-1 text-info',
                                'data-toggle' => 'tooltip', 
                                'title' => 'Este es el porcentaje total de comisión de la agencia.',
                                'data-placement' => 'top'
                            ]), [
                                'style' => 'font-size: 1.25rem !important; font-weight: bold;'
                            ])->textInput([
                                'class' => 'form-control',
                                'placeholder' => 'Ingrese el % por Agencia',
                                'type' => 'number',
                                'step' => '0.01',
                                'style' => 'font-size: 1.1rem !important; height: calc(2.25em + 1rem) !important;'
                            ]) ?>
                        </div>
                        <div class="col-6 col-sm-4 col-md-2">
                            <?= $form->field($model, 'por_max')->label('MÁXIMO ' . Html::tag('i', '', [
                                'class' => 'fas fa-info-circle ml-1 text-info',
                                'data-toggle' => 'tooltip', 
                                'title' => 'El porcentaje de comisión más alto que se puede asignar.',
                                'data-placement' => 'top'
                            ]), [
                                'style' => 'font-size: 1.25rem !important; font-weight: bold;'
                            ])->textInput([
                                'class' => 'form-control',
                                'placeholder' => 'Ingrese el % por Máximo',
                                'type' => 'number',
                                'step' => '0.01',
                                'style' => 'font-size: 1.1rem !important; height: calc(2.25em + 1rem) !important;'
                            ]) ?>
                        </div>
                    </div>
                 </div>
             </div>
         </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-12 d-flex justify-content-start">
            <?= Html::submitButton('<i class="fas fa-save mr-2"></i> Guardar', ['class' => 'btn btn-success btn-lg mr-3']) ?>
            <?= Html::a(
                '<i class="fas fa-undo mr-2"></i> Volver', 
                '#',
                [
                    'class' => 'btn btn-secondary btn-lg',
                    'onclick' => 'window.history.back(); return false;', 
                    'title' => 'Volver a la página anterior', 
                ]
            ) ?>
        </div>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php 
// JavaScript to initialize Bootstrap Tooltips
$this->registerJs('
    $(function () {
        $("[data-toggle=\'tooltip\']").tooltip()
    })
', View::POS_READY);

// JavaScript to handle the search and user creation logic
$js = <<<JS
$(function () {
    $("[data-toggle='tooltip']").tooltip();
    
    var cedulaSearch = $('#{$cedulaSearchId}');
    var searchBtn = $('#search-propietario-btn');
    var searchResult = $('#search-result');
    var propietarioSelection = $('#propietario-selection-row');
    var userCreateSection = $('#{$userCreateSectionId}');
    var propietarioField = $('#{$propietarioFieldId}');
    var createPropietarioBtn = $('#create-propietario-btn');
    var cancelCreateBtn = $('#cancel-create-btn');
    var newUserCedula = $('#new_user_cedula');
    
    console.log('=== DEBUG: Element IDs ===');
    console.log('propietarioField ID:', '{$propietarioFieldId}');
    console.log('propietarioField element:', propietarioField);
    console.log('propietarioField length:', propietarioField.length);
    console.log('Is Select2:', propietarioField.hasClass('select2-hidden-accessible'));
    console.log('User Create Section ID:', '{$userCreateSectionId}');
    console.log('User Create Section element:', userCreateSection);
    console.log('User Create Section length:', userCreateSection.length);
    
    // Get initial options from Select2 for debugging
    var initialOptions = propietarioField.find('option').map(function() {
        return { value: this.value, text: this.text };
    }).get();
    console.log('Initial Select2 options:', initialOptions);
    
    // Ensure creation section is hidden on page load
    userCreateSection.hide();
    
    // Search for propietario by cedula
    searchBtn.on('click', function() {
        var cedula = cedulaSearch.val().trim();
        
        if (!cedula) {
            showSearchResult('<div class="alert alert-danger" style="font-size: 1.5rem !important; padding: 1rem !important;"><i class="fas fa-exclamation-circle mr-2"></i>Por favor ingrese una cédula para buscar.</div>', 'danger');
            return;
        }
        
        searchBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Buscando...');
        
        $.ajax({
            url: '{$findByCedulaUrl}',
            type: 'GET',
            data: { cedula: cedula },
            beforeSend: function(xhr) {
                console.log('Sending AJAX request to:', '{$findByCedulaUrl}');
                console.log('With data:', { cedula: cedula });
            },
            success: function(response) {
                searchBtn.prop('disabled', false).html('<i class="fas fa-search"></i> Buscar');
                console.log('AJAX Success Response:', response);
                
                if (response.success && response.user) {
                    // Check if this user exists in the current Select2 options
                    var userExists = propietarioField.find('option[value="' + response.user.id + '"]').length > 0;
                    console.log('User exists in Select2 options:', userExists);
                    
                    if (userExists) {
                        // User exists in Select2 options - we can select it
                        showSearchResult(
                            '<div class="alert alert-success" style="font-size: 1.5rem !important; padding: 1.5rem !important;">' +
                            '<i class="fas fa-check-circle mr-2"></i>' +
                            '<strong>Usuario encontrado:</strong> ' + response.user.nombres + ' ' + response.user.apellidos +
                            '<br><strong>Cédula:</strong> ' + response.user.tipo_cedula + '-' + response.user.cedula +
                            '</div>' +
                            '<div class="text-center mt-3">' +
                            '<button type="button" class="btn btn-success btn-lg" id="assign-btn-' + response.user.id + '" style="font-size: 1.5rem !important; padding: 1rem 2rem !important;">' +
                            '<i class="fas fa-user-check mr-2"></i> Asignar como Propietario' +
                            '</button>' +
                            '</div>',
                            'success'
                        );
                        
                        // Add click handler to the assign button
                        $('#assign-btn-' + response.user.id).on('click', function() {
                            console.log('=== DEBUG: Assign button clicked ===');
                            console.log('User ID:', response.user.id);
                            console.log('User Name:', response.user.nombres + ' ' + response.user.apellidos);
                            assignPropietario(response.user.id);
                        });
                    } else {
                        // User doesn't exist in Select2 options - show creation section
                        showSearchResult(
                            '<div class="alert alert-warning" style="font-size: 1.5rem !important; padding: 1.5rem !important;">' +
                            '<i class="fas fa-exclamation-triangle mr-2"></i>' +
                            '<strong>Usuario encontrado pero no disponible:</strong> ' + response.user.nombres + ' ' + response.user.apellidos +
                            '</div>' +
                            '<div class="alert alert-info" style="font-size: 1.3rem !important; padding: 1.5rem !important;">' +
                            '<i class="fas fa-info-circle mr-2"></i>' +
                            'Este usuario existe en el sistema pero no está disponible como propietario. Puede crear un nuevo usuario o contactar al administrador.' +
                            '</div>' +
                            '<div class="text-center mt-3">' +
                            '<button type="button" class="btn btn-primary btn-lg mr-3" id="create-user-btn-' + cedula + '" style="font-size: 1.5rem !important; padding: 1rem 2rem !important;">' +
                            '<i class="fas fa-user-plus mr-2"></i> Crear Nuevo Usuario' +
                            '</button>' +
                            '</div>',
                            'warning'
                        );
                        
                        // Add click handler to the create user button
                        $('#create-user-btn-' + cedula).on('click', function() {
                            console.log('=== DEBUG: Create User button clicked ===');
                            console.log('Cedula:', cedula);
                            showUserCreationForm(cedula);
                        });
                    }
                    
                    // Hide user creation section if it was visible
                    userCreateSection.hide();
                    propietarioSelection.show();
                    
                } else {
                    // User not found
                    showSearchResult(
                        '<div class="alert alert-warning" style="font-size: 1.5rem !important; padding: 1.5rem !important;">' +
                        '<i class="fas fa-exclamation-triangle mr-2"></i>' +
                        '<strong>No se encontró ningún usuario con la cédula:</strong> ' + cedula +
                        '</div>' +
                        '<div class="text-center mt-3">' +
                        '<button type="button" class="btn btn-primary btn-lg" id="create-user-btn-' + cedula + '" style="font-size: 1.5rem !important; padding: 1rem 2rem !important;">' +
                        '<i class="fas fa-user-plus mr-2"></i> Crear Nuevo Usuario' +
                        '</button>' +
                        '</div>',
                        'warning'
                    );
                    
                    // Add click handler to the create user button
                    $('#create-user-btn-' + cedula).on('click', function() {
                        console.log('=== DEBUG: Create User button clicked ===');
                        console.log('Cedula:', cedula);
                        showUserCreationForm(cedula);
                    });
                }
            },
            error: function(xhr, status, error) {
                searchBtn.prop('disabled', false).html('<i class="fas fa-search"></i> Buscar');
                
                console.log('AJAX Error Details:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusText: xhr.statusText,
                    statusCode: xhr.status
                });
                
                let errorMessage = 'Error al buscar el usuario. Por favor, intente nuevamente.';
                
                // Check if it's a server error (500) or other HTTP error
                if (xhr.status >= 500) {
                    errorMessage = 'Error del servidor. Por favor, contacte al administrador.';
                } else if (xhr.status === 404) {
                    errorMessage = 'Servicio no encontrado. Verifique la configuración.';
                } else if (xhr.responseText) {
                    try {
                        let response = JSON.parse(xhr.responseText);
                        if (response.message) {
                            errorMessage = response.message;
                        }
                    } catch (e) {
                        // If not JSON, check if it's an HTML error page
                        if (xhr.responseText.includes('Exception') || xhr.responseText.includes('Error')) {
                            errorMessage = 'Error interno del servidor.';
                        }
                    }
                }
                
                showSearchResult(
                    '<div class="alert alert-danger" style="font-size: 1.5rem !important; padding: 1.5rem !important;">' +
                    '<i class="fas fa-exclamation-circle mr-2"></i>' +
                    errorMessage +
                    '</div>',
                    'danger'
                );
            }
        });
    });
    
    // Also search on Enter key in cedula field
    cedulaSearch.on('keypress', function(e) {
        if (e.which === 13) {
            e.preventDefault();
            searchBtn.click();
        }
    });
    
    // Create new propietario
    createPropietarioBtn.on('click', function() {
        var formData = {
            nombres: $('input[name="new_user_nombres"]').val(),
            apellidos: $('input[name="new_user_apellidos"]').val(),
            tipo_cedula: $('select[name="new_user_tipo_cedula"]').val(),
            cedula: $('input[name="new_user_cedula"]').val(),
            telefono: $('input[name="new_user_telefono"]').val(),
            email: $('input[name="new_user_email"]').val(),
            password: $('input[name="new_user_password"]').val(),
            role: 'Agente' // Predefined role
        };
        
        // Basic validation
        if (!formData.nombres || !formData.apellidos || !formData.cedula || !formData.email || !formData.password) {
            alert('Por favor complete todos los campos obligatorios (*)');
            return;
        }
        
        createPropietarioBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Creando...');
        
        $.ajax({
            url: '{$createAgenteUserUrl}',
            type: 'POST',
            data: formData,
            success: function(response) {
                createPropietarioBtn.prop('disabled', false).html('<i class="fas fa-user-check mr-2"></i> Crear Propietario y Asignar');
                
                if (response.success && response.user) {
                    // Successfully created user - we need to refresh the page or update the Select2
                    userCreateSection.hide();
                    showSearchResult(
                        '<div class="alert alert-success" style="font-size: 1.5rem !important; padding: 1.5rem !important;">' +
                        '<i class="fas fa-check-circle mr-2"></i>' +
                        '<strong>¡Usuario creado exitosamente!</strong><br>' +
                        'Nombre: ' + response.user.nombres + ' ' + response.user.apellidos + '<br>' +
                        'Email: ' + response.user.email + '<br><br>' +
                        '<em>Nota: Actualice la página para ver el nuevo usuario en la lista de propietarios.</em>' +
                        '</div>',
                        'success'
                    );
                } else {
                    alert('Error al crear el usuario: ' + (response.message || 'Error desconocido'));
                }
            },
            error: function(xhr) {
                createPropietarioBtn.prop('disabled', false).html('<i class="fas fa-user-check mr-2"></i> Crear Propietario y Asignar');
                var errorMessage = 'Error al crear el usuario';
                try {
                    var response = JSON.parse(xhr.responseText);
                    if (response.message) {
                        errorMessage = response.message;
                    }
                } catch (e) {}
                alert(errorMessage);
            }
        });
    });
    
    // Cancel user creation
    cancelCreateBtn.on('click', function() {
        userCreateSection.hide();
        searchResult.hide();
        cedulaSearch.val('').focus();
    });
    
    function showSearchResult(message, type) {
        searchResult.html(message).show();
    }
    
    // Define showUserCreationForm function - SIMPLIFIED VERSION
    function showUserCreationForm(cedula) {
        console.log('=== DEBUG: showUserCreationForm called ===');
        
        // Hide everything else
        $('#search-result').hide();
        $('#propietario-selection-row').hide();
        
        // Set the cedula value
        $('#new_user_cedula').val(cedula);
        
        // Simply remove the inline style that's hiding it and show it
        $('#{$userCreateSectionId}').removeAttr('style').show();
        
        console.log('User Create Section after show:', $('#{$userCreateSectionId}').is(':visible'));
        
        // Scroll to it
        $('html, body').animate({
            scrollTop: $('#{$userCreateSectionId}').offset().top - 100
        }, 500);
    }
    
    // Make function globally available
    window.showUserCreationForm = showUserCreationForm;
});

// Global functions for button onclick events
function assignPropietario(userId) {
    console.log('=== DEBUG: assignPropietario called ===');
    console.log('User ID:', userId);
    
    var propietarioField = $('#{$propietarioFieldId}');
    
    // Simple assignment - this should work if the user exists in Select2 options
    propietarioField.val(userId).trigger('change');
    
    console.log('Value after assignment:', propietarioField.val());
    console.log('Selected text:', propietarioField.find('option:selected').text());
    
    $('#search-result').hide();
    $('#propietario-selection-row').show();
    $('#{$userCreateSectionId}').hide();
    
    alert('¡Propietario asignado exitosamente!');
}
JS;

$this->registerJs($js);
?>