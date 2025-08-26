<?php
use yii\helpers\Html;
use Da\QrCode\QrCode;

// --- Preparación de la imagen en Base64 para incrustarla directamente ---
// IMPORTANTE: Asegúrate de que la ruta 'sipsa/web/img/huellasfirmas.png'
// sea accesible desde donde se ejecuta este script PHP.
// En Yii2, '@webroot/img/huellasfirmas.png' o '@app/web/img/huellasfirmas.png'
// suelen ser más seguros para rutas de archivos.
$imagePath = Yii::getAlias('@app/web/img/huellasfirmas.png'); // Usa esta si estás en un contexto Yii2 y apunta a la raíz web.
// Si no estás en Yii2 o la ruta es diferente, podrías necesitar una ruta absoluta:
// $imagePath = '/ruta/completa/a/tu/proyecto/sipsa/web/img/huellasfirmas.png';

$imageData = '';
$imageMimeType = '';

// Verifica si el archivo existe antes de intentar leerlo
if (file_exists($imagePath)) {
    $imageData = base64_encode(file_get_contents($imagePath));
    $imageMimeType = mime_content_type($imagePath);
    $base64ImageSrc = "data:{$imageMimeType};base64,{$imageData}";
} else {
    // Si la imagen no se encuentra, puedes poner un placeholder o registrar un error
    error_log("Error: La imagen de huellas no se encontró en la ruta: " . $imagePath);
    $base64ImageSrc = 'data:image/png;base64,iVBORw0KGgoAAAANSUlFTkLAAAAEAAAABjCB0C8AAAAASUVORK5CYII='; // Un pixel transparente como fallback
}
// --- Fin de la preparación de la imagen ---
?>

<table style="width: 100%; border-collapse: collapse;">
  <tr>
    <!-- Columna del logo: 20% de ancho, alineación vertical superior y horizontal izquierda, sin padding. -->
    <td style="width: 20%; vertical-align: top; padding: 0; text-align: left;">
      <!-- Imagen del logo: 110px de ancho fijo. display: block y margin: 0 para control de alineación. -->
      <img src="<?= Html::encode($logo) ?>" alt="Logo SISPSA" class="logo-superior-izquierda" style="width: 110px; height: auto; display: block; margin: 0;">
    </td>
    <!-- Columna del texto: 80% de ancho, alineación vertical superior, sin padding. -->
    <td style="width: 80%; vertical-align: top; padding: 0;">
      <!-- Tabla anidada para controlar mejor la alineación individual de cada texto. -->
      <table style="width: 100%; border-collapse: collapse;">
        <tr>
          <!-- Celda específica para el título, con alineación centrada y un pequeño espacio debajo. -->
          <td style="text-align: center; padding-bottom: 10px;">
            <div style="font-weight: bold; font-size: 16px;">DECLARACIÓN DE SALUD</div>
          </td>
        </tr>
        <tr>
          <!-- Celda específica para el párrafo, con alineación justificada. -->
          <td style="font-size: 10px; line-height: 1.5; text-align: justify;">
            De acuerdo con la información solicitada a continuación, la empresa Sistema Integral de Salud Programado Medicina Prepagada., S.A.,
            tomará la decisión de afiliar, no afiliar o condicionar el servicio al interesado en el Plan de Medicina Prepagada seleccionado, según el
            estudio médico resultante de la declaración de salud, ya que esta determina las condiciones en las que se contratará, tomando en
            cuenta las patologías preexistentes y las exclusiones aplicables. Es por ello que agradecemos que esta solicitud sea completada en
            todas sus partes con letra legible o de imprenta, sin enmienda, alteración, omisión, forjamiento, información falsa o fraudulenta.
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>

<?php /*
<div class="qr-container">
  <?php
  /*$qrCode = (new QrCode($qrData))
    ->setSize(80)
    ->setMargin(5);
  echo '<img src="' . $qrCode->writeDataUri() . '">';
  ?>
  <div style="font-size: 10px; text-align: center;">Verificación digital</div>
</div> */ ?>

<table class="tabla-declaracion">
  <tr>
    <th colspan="5">DECLARACIÓN JURADA</th>
  </tr>
  <tr>
    <th colspan="5">DECLARACIÓN DE SALUD DEL AFILIADO TITULAR Y/O FAMILIARES</th>
  </tr>
  <tr>
    <td colspan="5">
    RESPONDA EL CUESTIONARIO E INDIQUE SI USTED O CUALQUIERA DE LAS PERSONAS DE SU GRUPO FAMILIAR HAN PADECIDO, HAN SIDO DIAGNOSTICADOS, TRATADOS O MEDICADOS, POR ALGUNOS DE LOS SÍNTOMAS Y/O ENFERMEDADES DETALLADAS A CONTINUACIÓN (En caso afirmativo subraye la enfermedad que corresponda)
    </td>
  </tr>
  <tr>
    <th width="50%">DESCRIPCIÓN</th>
    <th width="10%">SI</th>
    <th width="10%">NO</th>
    <th width="30%">ESPECIFICACIONES</th>
  </tr>
  <tr>
    <td><span class="numero-enfermedad">1.</span> ESTATURA Y PESO</td>
    <td></td>
    <td></td>
    <td>Estatura: <?= Html::encode($model->estatura) ?> cm | Peso: <?= Html::encode($model->peso) ?> kg</td>
  </tr>
  <?php for ($i = 1; $i <= 16; $i++): ?>
  <tr>
    <td>
      <span class="numero-enfermedad"><?= $i+1 ?>.</span>
      <span class="negrita"><?= substr($preguntas[$i], 0, strpos($preguntas[$i], ':')) ?>:</span>
      <?= substr($preguntas[$i], strpos($preguntas[$i], ':')+1) ?>
    </td>
    <td class="centrado"><?= $model->{"p{$i}_sino"} === 'Si' ? 'X' : '' ?></td>
    <td class="centrado"><?= $model->{"p{$i}_sino"} === 'No' ? 'X' : '' ?></td>
    <td><?= Html::encode($model->{"p{$i}_especifica"}) ?></td>
  </tr>
  <?php endfor; ?>

  <!-- Sección de información personal y declaraciones -->
  <tr>
    <td colspan="5" style="padding-top: 15px;">
      <p style="text-align: justify; margin-bottom: 5px;">
        Yo, <?= Html::encode($afiliado->nombres . ' ' . $afiliado->apellidos) ?>, de nacionalidad venezolana, mayor de edad, Cédula de Identidad Nº <?= Html::encode($afiliado->cedula) ?>, RIF: _____
      </p>
      <p style="text-align: justify; margin-bottom: 5px;">a. Que he leído y entendido cabalmente cada una de las partes de esta solicitud y la información que suministro es verdadera, amplia, completa y exacta, dependiendo de ella la validez del contrato.</p>
      <p style="text-align: justify; margin-bottom: 5px;">b. Que acepto, que la responsabilidad de la prestadora del servicio comienza una vez procesado el pago del (los) plan (es) seleccionado (s) y recibida la notificación, vía correo electrónico, de la activación del servicio del Plan de Medicina Prepagada.</p>
      <p style="text-align: justify; margin-bottom: 5px;">c. Que no he omitido o simulado ningún hecho o circunstancia en las respuestas que pueda modificar la opinión médica del servicio, sobre el riesgo a correr por el Plan de Medicina Prepagada solicitado.</p>
      <p style="text-align: justify;">d. Renuncio expresamente a todos los beneficios del secreto profesional que las disposiciones legales impongan a los médicos que me han tratado o examinado, facultándolos para proporcionar a la prestadora del servicio cualquier información que éste les solicite y que pueda referirse a mi persona.</p>
    </td>
  </tr>

  <!-- Fila para el título "FIRMA Y HUELLAS DEL DECLARANTE" -->
  <tr>
    <td colspan="5" style="font-weight: bold; text-align: center; border-bottom: 1px solid #000; padding-top: 5px; padding-bottom: 3px;">FIRMA Y HUELLAS DEL DECLARANTE</td>
  </tr>

  <!-- Contenedor de la imagen de huellas y firmas -->
  <tr>
    <td colspan="5" style="text-align: center; padding: 0;">
      <table style="width: 100%; border-collapse: collapse;">
        <tr>
          <td style="text-align: center; padding-top: 10px; padding-bottom: 20px; border: none;">
            <!-- Tu imagen con las huellas y la firma, ahora incrustada con Base64 y tamaño ajustado -->
            <img src="<?= Html::encode($base64ImageSrc) ?>" alt="Huellas Dactilares y Firma" style="width: 500px; height: 180px; display: block; margin: 0 auto;">
            <?php if (empty($imageData)): ?>
                <div style="color: red; font-size: 12px; margin-top: 5px;">¡Advertencia! La imagen 'huellasfirmas.png' no fue encontrada. Verifica la ruta.</div>
            <?php endif; ?>
          </td>
        </tr>
      </table>
    </td>
  </tr>
</table>
