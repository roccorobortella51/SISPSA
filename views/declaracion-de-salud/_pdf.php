<?php
use yii\helpers\Html;
use Da\QrCode\QrCode;
?>
<img src="<?= Html::encode($logo) ?>" alt="Logo SISPSA" class="logo-superior-izquierda">

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

<div class="titulo-principal">DECLARACIÓN DE SALUD</div>

<div class="texto-justificado">
De acuerdo con la información solicitada a continuación, la empresa Sistema Integral de Salud Programado Medicina Prepagada., S.A.,
tomará la decisión de afiliar, no afiliar o condicionar el servicio al interesado en el Plan de Medicina Prepagada seleccionado, según el
estudio médico resultante de la declaración de salud, ya que esta determina las condiciones en las que se contratará, tomando en
cuenta las patologías preexistentes y las exclusiones aplicables. Es por ello que agradecemos que esta solicitud sea completada en
todas sus partes con letra legible o de imprenta, sin enmienda, alteración, omisión, forjamiento, información falsa o fraudulenta.
</div>

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
</table>

<div class="margen-superior texto-justificado">
Yo, <?= Html::encode($afiliado->nombres . ' ' . $afiliado->apellidos) ?>, de nacionalidad venezolana, mayor de edad, Cédula de Identidad Nº <?= Html::encode($afiliado->cedula) ?>, RIF: _____
</div>

<div class="margen-superior">
  <p>a. Que he leído y entendido cabalmente cada una de las partes de esta solicitud y la información que suministro es verdadera, amplia, completa y exacta, dependiendo de ella la validez del contrato.</p>

  <p>b. Que acepto, que la responsabilidad de la prestadora del servicio comienza una vez procesado el pago del (los) plan (es) seleccionado (s) y recibida la notificación, vía correo electrónico, de la activación del servicio del Plan de Medicina Prepagada.</p>

  <p>c. Que no he omitido o simulado ningún hecho o circunstancia en las respuestas que pueda modificar la opinión médica del servicio, sobre el riesgo a correr por el Plan de Medicina Prepagada solicitado.</p>

  <p>d. Renuncio expresamente a todos los beneficios del secreto profesional que las disposiciones legales impongan a los médicos que me han tratado o examinado, facultándolos para proporcionar a la prestadora del servicio cualquier información que éste les solicite y que pueda referirse a mi persona.</p>
</div>

<table class="tabla-firma">
  <tr>
    <td colspan="2" class="subtitulo">FIRMA Y HUELLAS DEL DECLARANTE</td>
  </tr>
  <tr>
    <td width="50%">Huella Dactilar</td>
    <td width="50%">Huella Dactilar</td>
  </tr>
  <tr>
    <td>Pulgar Izquierdo</td>
    <td>Pulgar Derecho</td>
  </tr>
  <tr>
    <td colspan="2" style="height: 50px;"></td>
  </tr>
  <tr>
    <td colspan="2" class="centrado"><br>_________________________________________<br>Firma<br></td>
  </tr>
</table>