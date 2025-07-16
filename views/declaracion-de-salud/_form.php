<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker; // Asegúrate de que esta extensión esté instalada si la usas en otro lado del formulario

/* @var $this yii\web\View */
/* @var $model app\models\DeclaracionSalud */ // Asegúrate de que el modelo sea correcto
/* @var $form yii\widgets\ActiveForm */
/* @var $afiliado app\models\UserDatos */ // Asegúrate de que el modelo sea correcto y lo estés pasando al renderizar la vista

// Define las opciones para "Sí/No" con los strings 'Si' y 'No'
$sinoOptions = [
    'Si' => 'Sí', // La clave 'Si' se guardará en DB, el valor 'Sí' se mostrará al usuario
    'No' => 'No', // La clave 'No' se guardará en DB, el valor 'No' se mostrará al usuario
];

// Define las preguntas para mostrar al usuario (NO para pre-cargar inputs)
$preguntas = [
    1 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES CARDIOVASCULARES: Hipertensión Arterial, infarto al Miocardio, Arritmia Cardíaca, Aneurisma, Palpitaciones, Angina de Pecho, Fiebre Reumática, Arteriesclerosis, Trastornos Valvulares, Tromboflebitis, Várices.',
    2 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES VASCULARES: Accidentes Vasculares, Hemiplejia, Parálisis, Hemorragias Cerebrales, Epilepsia o similares.',
    3 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES DE LA SANGRE: Leucemia, Sida o similares. Trastornos Valvulares, Tromboflebitis, Várices.',
    4 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES DE LAS VÍAS RESPIRATORIAS: Ronquera, tos persistente, bronquitis, asma, enfisema, tuberculosis, pleuresía, neumonía, bronconeumonía.',
    5 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES DE LAS VÍAS DIGESTIVAS: Gastritis, Úlceras, Hepatitis, Cirrosis, Hemorroides o similares, Apendicitis, colitis, Litiasis Vesicular, hernias hiatales, fisura anal.',
    6 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES DEL SISTEMA ENDOCRINO: Diabetes, Obesidad, Tiroides, Paratiroides, Bocio, Hipófisis, Alteraciones del Colesterol y Triglicéridos.',
    7 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES OSTEOMUSCULARES: Neuritis, Ciática, Reumatismo, Hernias Discales, Artritis, Osteoporosis, Desviación de la Columna Vertebral, Problemas en las Articulaciones.',
    8 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES GENITO-URINARIAS: Cálculos u otra alteración en los riñones, vejiga o próstata; prostatitis, varicocele, fimosis, parafimosis. Albúmina, sangre, pus, o infecciones en la orina.',
    9 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES DE LA PIEL, OJOS, OÍDOS, NARÍZ, GARGANTA: Desviación del Tabique Nasal, Sinusitis, Amigdalitis, Rinitis, Otitis, Cataratas, Hipertrofia de Cornetes, Timpanocentesis, Timpanoplastia o similares.',
    10 => 'Has sido diagnosticado con alguna ENFERMEDAD O DESORDEN MENTAL, DEFECTOS CONGÉNITOS O ADQUIRIDOS O SIMILARES.',
    11 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES TRANSITORIAS CRÓNICAS O ALGÚN DEFECTO NO MENCIONADOS ANTERIORMENTE.',
    12 => 'Has sido diagnosticado con alguna de las siguientes enfermedades: CÁNCER, TUMORES, GANGLIOS LINFÁTICOS INFLAMADOS, QUISTES, TUMORES BENIGNOS, ADENOMAS BENIGNOS DE LA MAMA O SIMILARES.',
    13 => 'Has sido diagnosticado con alguna de las siguientes ENFERMEDADES PROPIAS DE LA MUJER: Sangramiento Genital, Fibroma Uterino, Prolapso, Obstrucción en las Trompas, Ovarios Poliquísticos, Patologías Mamarias, Endometriosis, Incontinencia Urinaria, afecciones de las Glándulas Mamarias, Osteoporosis, Enfermedad Inflamatoria Pélvica, Pólipos Endometriales.',
    14 => 'Ha recibido TRANSFUSIONES DE SANGRE, QUIMIOTERAPIA, RADIOTERAPIA O SIMILARES.',
    15 => 'Le ha sido indicada o practicada alguna INTERVENCIÓN QUIRÚRGICA O SE HA SOMETIDO A TRATAMIENTO MÉDICO POR ALGUNA ENFERMEDAD O LESIÓN ADICIONAL A LAS ANTERIORES.',
    16 => 'Le ha sido diagnosticada alguna otra enfermedad o patología no mencionada anteriormente.',
];

?>

<div class="declaracion-salud-form">

    <?php $form = ActiveForm::begin(); ?>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-primary text-white">
            <h5 class="m-0 font-weight-bold">Datos Generales del Afiliado</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <?= $form->field($model, 'user_id')->hiddenInput(['value' => $afiliado->id])->label(false) ?>
                    <p><strong>Cédula:</strong> <?= Html::encode($afiliado->cedula) ?></p>
                </div>
                <div class="col-md-8">
                    <p><strong>Nombre del Afiliado:</strong> <?= Html::encode($afiliado->nombres . ' ' . $afiliado->apellidos) ?></p>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'estatura')->textInput(['type' => 'number', 'step' => '0.01', 'placeholder' => 'Ej: 1.75 (metros)']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'peso')->textInput(['type' => 'number', 'step' => '0.1', 'placeholder' => 'Ej: 70.5 (kilogramos)']) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-info text-white">
            <h5 class="m-0 font-weight-bold">Cuestionario de Salud</h5>
        </div>
        <div class="card-body">
            <p class="mb-4">Por favor, responda a cada pregunta marcando "Sí" o "No". En caso afirmativo, proporcione detalles en el campo de especificaciones.</p>

            <?php for ($i = 1; $i <= 16; $i++): ?>
                <div class="card mb-3 border-left-info">
                    <div class="card-body">
                        <div class="form-group mb-3">
                            <label class="form-label"><strong>Pregunta <?= $i ?>:</strong> <?= Html::encode($preguntas[$i]) ?></label>
                            <div class="d-flex mt-2"> <?= $form->field($model, "p{$i}_sino")->radioList($sinoOptions, [
                                    'item' => function($index, $label, $name, $checked, $value) {
                                        return '<div class="form-check me-4">' . // Agregamos margen con me-4
                                               Html::radio($name, $checked, [
                                                   'value' => $value,
                                                   'id' => 'radio_p' . ($index + 1) . '_' . $value, // ID único para el script
                                                   'class' => 'form-check-input'
                                               ]) .
                                               Html::label($label, 'radio_p' . ($index + 1) . '_' . $value, ['class' => 'form-check-label']) .
                                               '</div>';
                                    },
                                    'encode' => false, // IMPORTANTE: permite que la función item devuelva HTML
                                ])->label(false); ?>
                            </div>
                        </div>
                        
                        <?= $form->field($model, "p{$i}_especifica")->textarea([
                            'rows' => 3,
                            'placeholder' => 'Ingrese las especificaciones aquí si su respuesta fue "Sí".',
                            // Ya NO se usa 'value' aquí, el textarea se llenará con el valor del modelo si existe, sino estará vacío
                        ])->label('Especificaciones (obligatorio si la respuesta es Sí)'); ?>
                    </div>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-warning text-white">
            <h5 class="m-0 font-weight-bold">Información de Verificación y Estado</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'ver_observacion')->textarea(['rows' => 4, 'placeholder' => 'Observaciones de verificación']) ?>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="form-label">¿Verificación Final (Sí/No)?</label>
                        <div class="d-flex mt-2">
                            <?= $form->field($model, 'ver_si_no')->radioList($sinoOptions, [
                                'item' => function($index, $label, $name, $checked, $value) {
                                    return '<div class="form-check me-4">' .
                                           Html::radio($name, $checked, [
                                               'value' => $value,
                                               'id' => 'radio_ver_' . $value,
                                               'class' => 'form-check-input'
                                           ]) .
                                           Html::label($label, 'radio_ver_' . $value, ['class' => 'form-check-label']) .
                                           '</div>';
                                },
                                'encode' => false,
                            ])->label(false); ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-6">
                    <?= $form->field($model, 'url_video_declaracion')->textInput(['placeholder' => 'URL del video de la declaración (si aplica)']) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'estatus')->textInput(['value' => $model->isNewRecord ? 'En Proceso' : $model->estatus, 'readonly' => true]) ?>
                </div>
            </div>
        </div>
    </div>

    <div class="form-group text-end mt-4">
        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg me-2']) ?>
        <?= Html::a('Cancelar', ['index', 'user_id' => $afiliado->id], ['class' => 'btn btn-warning btn-lg me-2']); ?>
        <?php if ($model->isNewRecord): ?>
            <?= Html::a('Limpiar', ['create', 'user_id' => $afiliado->id], ['class' => 'btn btn-outline-dark btn-lg']); ?>
        <?php endif; ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
// Script para mostrar/ocultar el campo de especificación
// Asegúrate de que jQuery esté cargado en tu layout principal
$this->registerJs(<<<JS
    // Función para manejar la visibilidad de las especificaciones
    function toggleEspecifica(index) {
        var radioSi = $('input[name="DeclaracionSalud[p' + index + '_sino]"][value="Si"]');
        var especificaField = $('#declaracionsalud-p' + index + '_especifica').closest('.form-group');

        if (radioSi.is(':checked')) {
            especificaField.show();
            $('#declaracionsalud-p' + index + '_especifica').prop('required', true);
        } else {
            especificaField.hide();
            $('#declaracionsalud-p' + index + '_especifica').prop('required', false);
            // Solo limpiar el valor si no está ya vacío y se oculta el campo
            if ($('#declaracionsalud-p' + index + '_especifica').val() !== '') {
                $('#declaracionsalud-p' + index + '_especifica').val(''); 
            }
        }
    }

    // Inicializar al cargar la página
    for (var i = 1; i <= 16; i++) {
        toggleEspecifica(i); // Establecer el estado inicial
    }

    // Escuchar cambios en los radio buttons (para las preguntas del 1 al 16)
    $('input[name^="DeclaracionSalud[p"][name$="_sino]"]').on('change', function() {
        var name = $(this).attr('name');
        var index = name.match(/p(\d+)_sino/)[1];
        toggleEspecifica(index);
    });

    // Para el campo 'ver_si_no' que también es un radioList
    // (Puedes añadir lógica si 'ver_observacion' debe depender de 'ver_si_no')
    // Por ahora, 'ver_observacion' siempre visible.
JS
);
?>