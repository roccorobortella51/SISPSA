<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\date\DatePicker;

/* @var $this yii\web->View */
/* @var $model app->models->YourModelName */
/* @var $form yii->widgets->ActiveForm */

// Define las opciones para "Sí/No" con los strings 'Si' y 'No'
$sinoOptions = [
    'Si' => 'Sí', // La clave 'Si' se guardará en DB, el valor 'Sí' se mostrará al usuario
    'No' => 'No', // La clave 'No' se guardará en DB, el valor 'No' se mostrará al usuario
];

// Define las preguntas para los valores pre-cargados
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

<div class="your-form-container">

    <?php $form = ActiveForm::begin(); ?>

    <h3>Sección de Preguntas Sí/No</h3>
    <hr>

    <?php for ($i = 1; $i <= 16; $i++): ?>
        <div class="card mb-3">
            <div class="card-header">
                <h5 class="mb-0">Pregunta <?= $i ?></h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <?= $form->field($model, "p{$i}_sino")->radioList($sinoOptions, [
                            'itemOptions' => ['class' => 'me-3'],
                            'separator' => '<br>',
                        ])->label("¿Tuvo alguna afección para la pregunta {$i}?"); // Label genérico ?>
                    </div>
                    <div class="col-md-8">
                        <?= $form->field($model, "p{$i}_especifica")->textarea([
                            'rows' => 3,
                            'value' => $model->{"p{$i}_especifica"} ?: $preguntas[$i] // La pregunta como valor pre-cargado
                        ]) ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endfor; ?>

    <hr>
    <h3>Otros Datos</h3>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'ver_observacion')->textarea(['rows' => 3]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'ver_si_no')->radioList($sinoOptions, [
                'itemOptions' => ['class' => 'me-3'],
                'separator' => '<br>'
            ])->label('¿Verificado Sí/No?'); ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6">
            <?= $form->field($model, 'url_video_declaracion')->textarea(['rows' => 3]) ?>
        </div>
        <div class="col-md-6">
            <?= $form->field($model, 'estatus')->textInput(['value' => 'En Proceso']) ?>
        </div>
    </div>

    <div class="row">
        <div class="col-md-4" style="display:none;">
            <?= $form->field($model, 'user_id')->textInput(['value' => $afiliado->id, 'readonly' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'estatura')->textInput(['type' => 'decimal']) ?>
        </div>
        <div class="col-md-4">
            <?= $form->field($model, 'peso')->textInput(['type' => 'number']) ?>
        </div>
    </div>

    <div class="form-group text-rigth mt-4">
        <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg']) ?>
        <?= Html::a('Cancelar', ['index', 'user_id' => $afiliado->id], ['class' => 'tn btn btn-lg btn-warning']); ?>

        <?php if ($model->isNewRecord) { echo Html::a('Limpiar', ['create', 'user_id' => $afiliado->id], ['class' => 'btn btn-lg btn-outline-dark']); } ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>