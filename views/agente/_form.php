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

// URL variables for AJAX calls - ABSOLUTE URLs
$findByCedulaUrl = Url::to(['/user/find-by-cedula'], true);
$createUserUrl = Url::to(['/user/create'], true);

// Get agentes list for debugging
$agentesList = UserHelper::getAgentesList();
$agentesListCount = count($agentesList);
$agentesListSample = json_encode(array_slice($agentesList, 0, 5));

?>

<style>
    /* Professional Styling for Propietario Section */
    .bg-gradient-primary {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
    }

    .opacity-75 {
        opacity: 0.75;
    }

    .border-left-4 {
        border-left-width: 4px !important;
    }

    .border-left-4.border-info {
        border-left-color: #17a2b8 !important;
    }

    .border-left-4.border-success {
        border-left-color: #28a745 !important;
    }

    .border-left-4.border-warning {
        border-left-color: #ffc107 !important;
    }

    .border-left-4.border-danger {
        border-left-color: #dc3545 !important;
    }

    .step-circle {
        width: 45px;
        height: 45px;
        font-size: 1.2rem;
        font-weight: bold;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        margin: 0 auto 0.5rem auto;
        box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
    }

    .step-circle.step-1 {
        background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
        color: white;
    }

    .step-circle.step-2 {
        background: linear-gradient(135deg, #17a2b8 0%, #117a8b 100%);
        color: white;
    }

    .step-circle.step-3 {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        color: white;
    }

    .step-arrow {
        font-size: 2rem;
        color: #adb5bd;
    }

    .step-label {
        font-weight: bold;
        margin-bottom: 0.25rem;
        font-size: 0.9rem;
    }

    .step-description {
        font-size: 0.8rem;
        color: #6c757d;
    }

    .requirement-badge {
        font-size: 0.9rem;
        padding: 0.5rem 1.5rem;
        border-radius: 30px;
        background: white;
        color: #007bff;
        font-weight: bold;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .info-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        background: rgba(23, 162, 184, 0.1);
        color: #17a2b8;
    }

    .search-result .alert {
        border-left-width: 4px;
        transition: all 0.3s ease;
        margin-bottom: 1rem;
    }

    .search-result .btn {
        transition: all 0.3s ease;
        min-width: 200px;
    }

    .search-result .btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
    }

    .search-result .btn-primary:hover {
        background: #0069d9;
        border-color: #0062cc;
    }

    .search-result .btn-success:hover {
        background: #218838;
        border-color: #1e7e34;
    }

    .search-result .btn-secondary:hover {
        background: #5a6268;
        border-color: #545b62;
    }

    /* Ensure Select2 is visible */
    .select2-container {
        width: 100% !important;
        display: block !important;
    }

    .select2-container .select2-selection--single {
        height: calc(2.5em + 0.75rem + 2px) !important;
        padding: 0.375rem 0.75rem !important;
        font-size: 1.25rem !important;
        border: 1px solid #ced4da !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: calc(2.5em + 0.75rem) !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: calc(2.5em + 0.75rem + 2px) !important;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .step-circle {
            width: 35px;
            height: 35px;
            font-size: 1rem;
        }

        .step-arrow {
            font-size: 1.5rem;
        }

        .step-label {
            font-size: 0.8rem;
        }

        .step-description {
            font-size: 0.7rem;
        }

        .requirement-badge {
            font-size: 0.8rem;
            padding: 0.3rem 1rem;
        }
    }
</style>

<!-- DEBUG INFO - Remove in production -->
<!-- Agentes List Count: <?= $agentesListCount ?> -->
<!-- Agentes List Sample: <?= $agentesListSample ?> -->

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

                    <!-- ============================================ -->
                    <!-- PROPIETARIO REQUIREMENT SECTION - PROFESSIONAL -->
                    <!-- ============================================ -->
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <!-- Main Information Card -->
                            <div class="card shadow-sm border-0 overflow-hidden mt-4">
                                <div class="card-header bg-gradient-primary text-white py-3" style="background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);">
                                    <div class="d-flex align-items-center">
                                        <div class="mr-3">
                                            <i class="fas fa-user-tie fa-2x"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-0 font-weight-bold" style="font-size: 1.4rem;">Propietario de la Agencia</h5>
                                            <p class="mb-0 small" style="color: white !important; opacity: 1;">Requisito fundamental para el registro de una agencia</p>
                                        </div>
                                        <div class="ml-auto">
                                            <span class="badge badge-light px-4 py-1" style="font-size: 0.9rem;">
                                                <i class="fas fa-check-circle text-success mr-1"></i>
                                                <p style="color: white !important; opacity: 1;">Rol requerido: Agente
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body bg-light">
                                    <div class="container-fluid p-0">
                                        <!-- Quick Steps Visual Guide -->
                                        <div class="row mb-4">
                                            <div class="col-md-12">
                                                <div class="d-flex justify-content-around align-items-center">
                                                    <div class="text-center">
                                                        <div class="step-circle step-1">1</div>
                                                        <p class="step-label">Buscar Cédula</p>
                                                        <p class="step-description">Ingrese el número</p>
                                                    </div>
                                                    <div class="step-arrow">
                                                        <i class="fas fa-arrow-right"></i>
                                                    </div>
                                                    <div class="text-center">
                                                        <div class="step-circle step-2">2</div>
                                                        <p class="step-label">Verificar Existencia</p>
                                                        <p class="step-description">¿Usuario con rol Agente?</p>
                                                    </div>
                                                    <div class="step-arrow">
                                                        <i class="fas fa-arrow-right"></i>
                                                    </div>
                                                    <div class="text-center">
                                                        <div class="step-circle step-3">3</div>
                                                        <p class="step-label">Asignar o Crear</p>
                                                        <p class="step-description">Según el resultado</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Detailed Explanation -->
                                        <div class="alert alert-info border-left-4 border-info rounded-0 bg-white mb-4 py-3">
                                            <div class="d-flex">
                                                <div class="info-icon mr-3">
                                                    <i class="fas fa-info-circle fa-lg"></i>
                                                </div>
                                                <div>
                                                    <strong class="d-block" style="font-size: 1.1rem;">¿Por qué es necesario un Propietario?</strong>
                                                    <p class="mb-0 text-muted">
                                                        Cada agencia debe estar vinculada a un usuario del sistema que actúe como su propietario.
                                                        Este usuario debe tener el rol <strong class="text-primary">"Agente"</strong> en el sistema.
                                                        Si el propietario no existe, puede crearlo directamente desde este formulario.
                                                    </p>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Search Field -->
                                        <div class="form-group">
                                            <label class="font-weight-bold text-primary" style="font-size: 1.1rem;">
                                                <i class="fas fa-search mr-2"></i> Buscar Propietario por Cédula
                                                <span class="text-danger ml-2">*</span>
                                            </label>
                                            <div class="input-group input-group-lg">
                                                <?= Html::textInput('cedula_search', '', [
                                                    'id' => $cedulaSearchId,
                                                    'class' => 'form-control form-control-lg border-primary',
                                                    'placeholder' => 'Ejemplo: 12345678',
                                                    'style' => 'font-size: 1.2rem !important;'
                                                ]) ?>
                                                <div class="input-group-append">
                                                    <?= Html::button('<i class="fas fa-search mr-2"></i> Buscar', [
                                                        'id' => 'search-propietario-btn',
                                                        'class' => 'btn btn-primary px-4',
                                                        'style' => 'font-size: 1.2rem !important;'
                                                    ]) ?>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted mt-2">
                                                <i class="fas fa-lightbulb text-warning mr-1"></i>
                                                Ingrese la cédula sin puntos ni guiones. Si el usuario no existe, podrá crearlo.
                                            </small>
                                        </div>

                                        <!-- Search Results Container -->
                                        <div id="search-result" class="search-result mt-3" style="display: none;"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Existing Propietario Selection Field - FIXED VERSION -->
                    <div class="row" id="propietario-selection-row">
                        <div class="col-md-12">
                            <?php
                            // Ensure we have at least the default option
                            if (empty($agentesList)) {
                                $agentesList = ['0' => 'No Asignado'];
                            }

                            echo $form->field($model, 'idusuariopropietario')
                                ->label('PROPIETARIO ASIGNADO', ['style' => 'font-size: 1.5rem !important; font-weight: bold;'])
                                ->widget(Select2::classname(), [
                                    'data' => $agentesList,
                                    'options' => [
                                        'placeholder' => 'Seleccione un propietario existente',
                                        'class' => 'form-control',
                                        'style' => 'font-size: 1.25rem !important;',
                                        'id' => $propietarioFieldId
                                    ],
                                    'pluginOptions' => [
                                        'allowClear' => false,
                                        'width' => '100%',
                                    ],
                                    'pluginEvents' => [
                                        "select2:open" => "function() { console.log('Select2 opened'); }",
                                        "select2:select" => "function(e) { console.log('Selected:', e.params.data); }",
                                    ]
                                ]);
                            ?>
                            <small class="form-text text-muted">
                                <i class="fas fa-info-circle text-info mr-1"></i>
                                Seleccione un propietario de la lista. Si no aparece, búsquelo por cédula arriba.
                            </small>
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
                    <h5 class="m-0" style="font-size: 1.5rem !important;"> Porcentajes (%) de Comisiónes</h5>
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
    var propietarioField = $('#{$propietarioFieldId}');
    
    console.log('=== DEBUG: Element IDs ===');
    console.log('propietarioField ID:', '{$propietarioFieldId}');
    console.log('propietarioField element:', propietarioField);
    console.log('propietarioField length:', propietarioField.length);
    console.log('propietarioField HTML:', propietarioField.prop('outerHTML'));
    
    // Check if Select2 is initialized
    console.log('Is Select2 initialized?', propietarioField.hasClass('select2-hidden-accessible'));
    console.log('Select2 instance:', propietarioField.data('select2'));
    
    // If not initialized, initialize it manually
    if (propietarioField.length && !propietarioField.hasClass('select2-hidden-accessible')) {
        console.log('Manually initializing Select2...');
        propietarioField.select2({
            placeholder: 'Seleccione un propietario existente',
            allowClear: false,
            width: '100%',
            language: {
                noResults: function() {
                    return "No se encontraron resultados";
                }
            }
        });
    }
    
    // Log available options
    var options = propietarioField.find('option').map(function() {
        return { value: this.value, text: this.text };
    }).get();
    console.log('Available options:', options);
    console.log('Total options count:', options.length);
    
    // Helper function to show notifications
    function showNotification(type, message) {
        if (type === 'success') {
            alert('✅ ¡ÉXITO!\\n\\n' + message);
        } else {
            alert('❌ ERROR\\n\\n' + message);
        }
    }
    
    // Search for propietario by cedula
    searchBtn.on('click', function() {
        var cedula = cedulaSearch.val().trim();
        
        if (!cedula) {
            showSearchResult(
                '<div class="alert alert-danger border-left-4 border-danger" style="font-size: 1.2rem !important; padding: 1.5rem !important;">' +
                '<div class="d-flex align-items-center">' +
                '<i class="fas fa-exclamation-circle fa-2x text-danger mr-3"></i>' +
                '<div><strong>Error</strong><br>Por favor ingrese una cédula para buscar.</div>' +
                '</div></div>', 
                'danger'
            );
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
                searchBtn.prop('disabled', false).html('<i class="fas fa-search mr-2"></i> Buscar');
                console.log('AJAX Success Response:', response);
                
                if (response.success && response.user) {
                    // Check if this user exists in the current Select2 options
                    var userExists = propietarioField.find('option[value="' + response.user.id + '"]').length > 0;
                    console.log('User exists in Select2 options:', userExists);
                    
                    if (userExists) {
                        // User exists in Select2 options - we can select it
                        showSearchResult(
                            '<div class="alert alert-success border-left-4 border-success" style="font-size: 1.2rem !important; padding: 1.5rem !important;">' +
                            '<div class="d-flex align-items-center mb-3">' +
                            '<i class="fas fa-check-circle fa-2x text-success mr-3"></i>' +
                            '<div><strong>¡Usuario encontrado!</strong><br>' +
                            response.user.nombres + ' ' + response.user.apellidos + '<br>' +
                            '<span class="text-muted">Cédula: ' + response.user.tipo_cedula + '-' + response.user.cedula + '</span>' +
                            '</div></div>' +
                            '<div class="text-center mt-3">' +
                            '<button type="button" class="btn btn-success btn-lg px-5" id="assign-btn-' + response.user.id + '" style="font-size: 1.2rem !important;">' +
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
                        // User doesn't exist in Select2 options
                        showSearchResult(
                            '<div class="alert alert-warning border-left-4 border-warning" style="font-size: 1.2rem !important; padding: 1.5rem !important;">' +
                            '<div class="d-flex align-items-center">' +
                            '<i class="fas fa-exclamation-triangle fa-2x text-warning mr-3"></i>' +
                            '<div><strong>Usuario encontrado pero no disponible</strong><br>' +
                            response.user.nombres + ' ' + response.user.apellidos + '<br>' +
                            '<span class="text-muted">Cédula: ' + response.user.tipo_cedula + '-' + response.user.cedula + '</span><br>' +
                            '<span class="text-muted small">Este usuario existe en el sistema pero no está configurado como propietario.</span>' +
                            '</div></div>',
                            'warning'
                        );
                    }
                    
                } else {
                    // USER NOT FOUND - Show message with option to create new Agente
                    showSearchResult(
                        '<div class="alert alert-warning border-left-4 border-warning" style="font-size: 1.2rem !important; padding: 1.5rem !important;">' +
                        '<div class="d-flex align-items-center mb-4">' +
                        '<i class="fas fa-exclamation-triangle fa-2x text-warning mr-3"></i>' +
                        '<div><strong>Usuario no encontrado</strong><br>' +
                        'No existe ningún usuario con la cédula: <strong>' + cedula + '</strong>' +
                        '</div></div>' +
                        '<div class="text-center">' +
                        '<p class="mb-4">¿Desea crear un nuevo Agente con esta cédula?</p>' +
                        '<button type="button" class="btn btn-primary btn-lg px-5 mr-3" id="create-agente-yes-btn" style="font-size: 1.2rem !important;">' +
                        '<i class="fas fa-check-circle mr-2"></i> Sí, crear nuevo Agente' +
                        '</button>' +
                        '<button type="button" class="btn btn-secondary btn-lg px-5" id="create-agente-no-btn" style="font-size: 1.2rem !important;">' +
                        '<i class="fas fa-times-circle mr-2"></i> No, cancelar' +
                        '</button>' +
                        '</div>',
                        'warning'
                    );
                    
                    // Add click handler for "Sí" button
                    $('#create-agente-yes-btn').on('click', function() {
                        // Redirect to user creation page with parameters
                        var createUrl = '{$createUserUrl}?cedula=' + encodeURIComponent(cedula) + '&role=Agente&from=agente';
                        window.location.href = createUrl;
                    });
                    
                    // Add click handler for "No" button
                    $('#create-agente-no-btn').on('click', function() {
                        // Just clear the search result
                        searchResult.fadeOut();
                    });
                }
            },
            error: function(xhr, status, error) {
                searchBtn.prop('disabled', false).html('<i class="fas fa-search mr-2"></i> Buscar');
                
                console.log('AJAX Error Details:', {
                    status: status,
                    error: error,
                    responseText: xhr.responseText,
                    statusText: xhr.statusText,
                    statusCode: xhr.status
                });
                
                let errorMessage = 'Error al buscar el usuario. Por favor, intente nuevamente.';
                let errorTitle = 'Error del Sistema';
                
                // Check if it's a server error (500) or other HTTP error
                if (xhr.status === 403) {
                    errorTitle = 'Error de Permisos';
                    errorMessage = 'No tiene permisos para realizar esta búsqueda (403). Por favor, contacte al administrador.';
                } else if (xhr.status === 401) {
                    errorTitle = 'Sesión Expirada';
                    errorMessage = 'Su sesión ha expirado (401). Por favor, recargue la página y vuelva a iniciar sesión.';
                } else if (xhr.status >= 500) {
                    errorTitle = 'Error del Servidor';
                    errorMessage = 'Error interno del servidor (500). Por favor, contacte al administrador.';
                } else if (xhr.status === 404) {
                    errorTitle = 'Servicio no Encontrado';
                    errorMessage = 'El servicio de búsqueda no está disponible (404). Verifique la configuración.';
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
                    '<div class="alert alert-danger border-left-4 border-danger" style="font-size: 1.2rem !important; padding: 1.5rem !important;">' +
                    '<div class="d-flex align-items-center">' +
                    '<i class="fas fa-exclamation-circle fa-2x text-danger mr-3"></i>' +
                    '<div><strong>' + errorTitle + '</strong><br>' + errorMessage + '</div>' +
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
    
    function showSearchResult(message, type) {
        searchResult.html(message).fadeIn();
        
        // Scroll to the result
        $('html, body').animate({
            scrollTop: searchResult.offset().top - 100
        }, 500);
    }
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
    
    // Show success message
    alert('✅ ¡ÉXITO!\\n\\n¡Propietario asignado exitosamente!');
}
JS;

$this->registerJs($js);
?>