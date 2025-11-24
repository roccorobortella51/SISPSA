<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\ArrayHelper;

/* @var $this yii\web\View */
/* @var $model app\models\MasivoAfiliadosForm */
/* @var $corporativos array Lista de corporativos activos */

$this->title = 'Gestión: Carga Masiva de Afiliados Corporativos';
$this->params['breadcrumbs'][] = $this->title;

// Nota: Se asume que el tema de Yii o Bootstrap ya maneja iconos (fas) y clases de diseño (shadow, d-flex).
?>

<div class="corporativo-carga-masiva-form p-4">

    <!-- Encabezado y Acción de Descarga -->
    <div class="d-flex justify-content-between align-items-center mb-5 border-bottom pb-3">
        <h1 class="text-primary font-weight-bold"><i class="fas fa-layer-group me-2"></i> <?= Html::encode($this->title) ?></h1>
        <?= Html::a(
            '<i class="fas fa-file-download me-2"></i> Descargar Plantilla CSV Oficial',
            ['/corporativo/descargar-plantilla'], 
            [
                'class' => 'btn btn-success btn-lg shadow-sm', // Botón principal de descarga en verde
                'title' => 'Descarga el formato CSV con todas las columnas'
            ]
        ) ?>
    </div>

    <!-- Guía de Instrucciones (Tarjeta Azul) -->
    <div class="card bg-light border-primary mb-5 shadow-sm">
        <div class="card-body">
            <h3 class="card-title text-primary mb-3"><i class="fas fa-info-circle me-2"></i> Instrucciones y Formato Requerido</h3>
            <br>
            
            <p>Utilice la plantilla oficial (botón verde) para asegurar el orden y la cabecera correcta de las columnas. Recuerde la validación de clínicas.</p>
            
            <!-- Tabla de Campos -->
            <table class="table table-bordered table-sm mt-3">
                <thead class="bg-info text-white">
                    <tr>
                        <th style="width: 25%;">Campo</th>
                        <th style="width: 25%;">Tipo</th>
                        <th>Descripción y Formato</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="table-primary fw-bold">
                        <td colspan="3">CAMPOS REQUERIDOS (Obligatorios para el registro)</td>
                    </tr>
                    <tr><td>`tipo_cedula`</td><td>Texto</td><td>Tipo de documento de identidad (ej: C, E, P).</td></tr>
                    <tr><td>`cedula`</td><td>Texto</td><td>Número de cédula o documento.</td></tr>
                    <tr><td>`nombres`</td><td>Texto</td><td>Nombres completos del afiliado.</td></tr>
                    <tr><td>`apellidos`</td><td>Texto</td><td>Apellidos completos del afiliado.</td></tr>
                    <tr><td>`fechanac`</td><td>Fecha</td><td>Fecha de nacimiento. **Formato estricto: YYYY-MM-DD** (ej: 1990-05-15).</td></tr>
                    <tr><td>`sexo`</td><td>Texto</td><td>Género (M o F).</td></tr>
                    <tr><td>`telefono`</td><td>Texto</td><td>Teléfono de contacto (residencia o móvil).</td></tr>
                    <tr><td>`email`</td><td>Texto</td><td>Correo electrónico. **Debe ser único en el sistema.**</td></tr>
                    <tr><td>`direccion`</td><td>Texto</td><td>Dirección de residencia o cobro.</td></tr>
                    <tr><td>`plan_id`</td><td>Número</td><td>**ID del Plan** al que se afiliará. (Requiere clínica vinculada y autorizada).</td></tr>
                    
                    <tr class="table-secondary fw-bold">
                        <td colspan="3">CAMPOS OPCIONALES (Para información de contrato y oficina)</td>
                    </tr>
                    <tr><td>`asesor_id`</td><td>Número</td><td>ID del asesor (si aplica).</td></tr>
                    <tr><td>`asesor_nombre`</td><td>Texto</td><td>Nombre del asesor.</td></tr>
                    <tr><td>`fecha_inicio_contrato`</td><td>Fecha</td><td>Fecha de inicio de vigencia. Formato: YYYY-MM-DD.</td></tr>
                    <tr><td>`fecha_vencimiento_contrato`</td><td>Fecha</td><td>Fecha de vencimiento. Formato: YYYY-MM-DD.</td></tr>
                    <tr><td>`direccion_oficina`</td><td>Texto</td><td>Dirección de la oficina del afiliado.</td></tr>
                    <tr><td>`telefono_oficina`</td><td>Texto</td><td>Teléfono de la oficina del afiliado.</td></tr>
                    <tr><td>`tipo_sangre`</td><td>Texto</td><td>Tipo de sangre (ej: A+, O-).</td></tr>
                    <tr><td>`rol_en_corporativo`</td><td>Texto</td><td>Posición o rol del afiliado dentro de la empresa.</td></tr>
                </tbody>
            </table>

            <p class="mt-4 alert alert-warning mb-0">
                **VALIDACIÓN CRÍTICA:** La clínica asociada al `plan_id` debe estar previamente autorizada y vinculada al **Corporativo Destino** que seleccione en el formulario.
            </p>
        </div>
    </div>

    <!-- Formulario de Carga (Tarjeta de Éxito) -->
    <?php $form = ActiveForm::begin([
        'options' => ['enctype' => 'multipart/form-data'],
        'id' => 'carga-masiva-form'
    ]); ?>

    <div class="card shadow-lg border-success">
        <div class="card-header bg-success text-white">
            <h4 class="mb-0"><i class="fas fa-cloud-upload-alt me-2"></i> Proceso de Carga</h4>
        </div>
        <div class="card-body">
            
            <!-- 1. Campo de Selección de Corporativo -->
            <?= $form->field($model, 'corporativo_id')->dropDownList(
                $corporativos,
                ['prompt' => '--- Seleccione el Corporativo Destino para los afiliados ---', 'class' => 'form-control form-select-lg']
            )->label('<span class="fw-bold text-dark"><i class="fas fa-building me-2"></i> 1. Corporativo Destino</span>') ?>
            
            <!-- 2. Campo de Subida de Archivo -->
            <?= $form->field($model, 'masivoFile')->fileInput([
                'class' => 'form-control form-control-lg'
            ])->label('<span class="fw-bold text-dark"><i class="fas fa-file-csv me-2"></i> 2. Seleccionar Archivo CSV</span>')->hint('El archivo debe ser CSV y no debe superar los 5MB de tamaño.') ?>

            <div class="form-group pt-4 text-center">
                <?= Html::submitButton('<i class="fas fa-paper-plane me-2"></i> Procesar Carga', [
                    'class' => 'btn btn-primary btn-xl shadow-sm', // Botón de acción principal
                    'data-confirm' => 'ADVERTENCIA: ¿Está seguro de que desea iniciar la carga masiva? Esto creará nuevos usuarios y afiliados en el sistema.'
                ]) ?>
            </div>

        </div>
    </div>

    <?php ActiveForm::end(); ?>

</div>