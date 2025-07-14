<?php

use yii\helpers\Html;
use yii\helpers\Url; // Necesario para generar URLs

/** @var yii\web\View $this */
/** @var app\models\DeclaracionDeSalud $model */
/** @var app\models\User $afiliado // Asumiendo que puedes pasar el modelo de usuario relacionado si lo necesitas para mostrar el nombre, etc. */

$this->title = 'Declaración de Salud: ' . $afiliado->nombres." ".$afiliado->apellidos;
$this->params['breadcrumbs'][] = ['label' => 'Declaraciones de Salud', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Puedes registrar assets CSS/JS aquí si tienes estilos personalizados
\yii\web\YiiAsset::register($this);
// Para íconos de Font Awesome, asegúrate de tenerlos registrados o incluidos globalmente
// \rmrevin\yii\fontawesome\AssetBundle::register($this);

// Define las preguntas para mostrar junto a los campos específicos
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

// Intenta cargar el modelo de usuario asociado si existe


?>
<div class="declaracion-de-salud-view-custom container mt-4">

<div class="d-flex justify-content-between align-items-center mb-4">
    <h3><?= Html::encode($this->title) ?></h3><br>
    <div class="d-flex align-items-center">
        <?= Html::a('<i class="fas fa-edit"></i> Actualizar', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm my-spaced-button']) // Agregamos 'my-spaced-button' ?>
        <?= Html::a('<i class="fas fa-trash-alt"></i> Eliminar', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger btn-sm my-spaced-button', // Agregamos 'my-spaced-button'
            'data' => [
                'confirm' => '¿Está seguro de que desea eliminar esta Declaración de Salud?',
                'method' => 'post',
            ],
        ]) ?>
        <?= Html::a('<i class="fas fa-list"></i> Volver', ['index', 'user_id' => $afiliado->id], ['class' => 'btn btn-outline-secondary btn-sm']) // El último no necesita margen derecho ?>
    </div>
</div>

    <hr>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0"><i class="fas fa-info-circle"></i> Información General</h4>
                </div>
                <div class="card-body">
                    <p><strong>ID:</strong> <?= Html::encode($model->id) ?></p>
                    <p><strong>Fecha de Creación:</strong> <?= Html::encode($model->created_at) ?></p>
                    <p><strong>Última Actualización:</strong> <?= Html::encode($model->updated_at) ?></p>
                    <?php if (!empty($model->deleted_at)): ?>
                        <p><strong>Fecha de Eliminación:</strong> <?= Html::encode($model->deleted_at) ?></p>
                    <?php endif; ?>
                    <p><strong>Estatus:</strong> <?= Html::encode($model->estatus) ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-info text-white">
                    <h4 class="mb-0"><i class="fas fa-user-circle"></i> Datos del Usuario y Medidas</h4>
                </div>
                <div class="card-body">
                        <p><strong>Usuario Afiliado:</strong> <?= Html::encode($afiliado->nombres . '  ' . $afiliado->apellidos . ')') ?></p>
                        <?php // <p><strong>Nombre Completo:</strong> Html::encode($afiliado->nombre_completo)</p> ?>
                        <p><strong>Documento:</strong> <?= Html::encode($afiliado->cedula) ?></p>
                        <p><strong>Teléfono:</strong> <?= Html::encode($afiliado->telefono) ?></p>
                    <p><strong>Estatura:</strong> <?= Html::encode($model->estatura) ?> cm</p>
                    <p><strong>Peso:</strong> <?= Html::encode($model->peso) ?> kg</p>
                </div>
            </div>
        </div>
    </div>

    <hr>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-clipboard-question"></i> Respuestas a Preguntas de Salud</h4>
        </div>
        <div class="card-body">
            <?php for ($i = 1; $i <= 16; $i++): ?>
                <div class="mb-3 p-3 border rounded <?= ($model->{"p{$i}_sino"} === 'No') ? 'border-danger' : 'border-success' ?>">
                    <h5>Pregunta <?= $i ?>: <small class="text-muted"><?= Html::encode($preguntas[$i]) ?></small></h5>
                    <p>
                        <strong>Respuesta:</strong>
                        <span class="badge <?= ($model->{"p{$i}_sino"} === 'No') ? 'bg-danger' : 'bg-success' ?>">
                            <?= Html::encode($model->{"p{$i}_sino"}) ?>
                        </span>
                    </p>
                    <?php if (!empty($model->{"p{$i}_especifica"})): ?>
                        <p><strong>Especificación:</strong></p>
                        <div class="alert alert-info" role="alert">
                            <?= nl2br(Html::encode($model->{"p{$i}_especifica"})) ?>
                        </div>
                    <?php else: ?>
                        <p><em class="text-muted">No se proporcionó una especificación para esta pregunta.</em></p>
                    <?php endif; ?>
                </div>
            <?php endfor; ?>
        </div>
    </div>

    <hr>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-warning text-dark">
            <h4 class="mb-0"><i class="fas fa-clipboard-check"></i> Información de Verificación y Video</h4>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <p><strong>Verificado Sí/No:</strong>
                        <span class="badge <?= ($model->ver_si_no === 'Si') ? 'bg-success' : 'bg-danger' ?>">
                            <?= Html::encode($model->ver_si_no) ?>
                        </span>
                    </p>
                    <?php if (!empty($model->ver_observacion)): ?>
                        <p><strong>Observación de Verificación:</strong></p>
                        <div class="alert alert-secondary" role="alert">
                            <?= nl2br(Html::encode($model->ver_observacion)) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($model->ver_fecha)): ?>
                        <p><strong>Fecha de Verificación:</strong> <?= Html::encode($model->ver_fecha) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($model->ver_usuario_id)): ?>
                        <p><strong>Usuario Verificador ID:</strong> <?= Html::encode($model->ver_usuario_id) ?></p>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <?php if (!empty($model->url_video_declaracion)): ?>
                        <p><strong>URL del Video de Declaración:</strong></p>
                        <a href="<?= Html::encode($model->url_video_declaracion) ?>" target="_blank" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-video"></i> Ver Video
                        </a>
                        <p class="mt-2 text-muted small"><?= Html::encode($model->url_video_declaracion) ?></p>
                    <?php else: ?>
                        <p><em>No se proporcionó URL de video de declaración.</em></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>