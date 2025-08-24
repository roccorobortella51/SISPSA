<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use kartik\date\DatePicker;
use yii\web\View;

/* @var $this yii\web\View */
/* @var $model app\models\SisSiniestro */
/* @var $form yii\widgets\ActiveForm */
/* @var $afiliado app\models\UserDatos */

// CSS personalizado
$css = <<<CSS
.sis-siniestro-form {
    background-color: #f8f9fa;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}



.section-title {
    font-size: 18px;
    font-weight: 600;
    margin: 0;
    display: flex;
    align-items: center;
}

.section-title i {
    margin-right: 10px;
    font-size: 20px;
}

.text-blue-600 {
    color: white !important;
}



.select2-container--krajee .select2-selection--multiple,
.select2-container--krajee .select2-selection--single {
    border-radius: 8px;
    border: 1px solid #ced4da;
    padding: 6px 15px;
    min-height: 48px;
    display: flex;
    align-items: center;
}

.select2-container--krajee .select2-selection--multiple .select2-selection__choice {
    border-radius: 6px;
    background-color: #e9f2ff;
    border: 1px solid #c5d9f8;
    color: #2c3e50;
    padding: 3px 8px;
}

.btn {
    border-radius: 8px;
    padding: 12px 24px;
    font-weight: 600;
    transition: all 0.3s;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn i {
    margin-right: 8px;
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn-warning {
    background-color: #ffc107;
    border-color: #ffc107;
    color: #212529;
}

.btn-warning:hover {
    background-color: #e0a800;
    border-color: #d39e00;
    color: #212529;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.btn-outline-dark {
    border-color: #343a40;
    color: #343a40;
}

.btn-outline-dark:hover {
    background-color: #343a40;
    color: white;
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.afiliado-container {
    background-color: #f8f9fa;
    border-radius: 8px;
    padding: 15px;
    border-left: 4px solid #4a90e2;
    max-height: 600px;
    overflow-y: auto;
}

/* Mejoras para la vista del afiliado */
.afiliado-container .card {
    box-shadow: none;
    border: 1px solid #e3e6f0;
    border-radius: 8px;
}

.afiliado-container .card-header {
    background: linear-gradient(135deg, #4a90e2 0%, #2c3e50 100%);
    color: white;
    border-radius: 8px 8px 0 0 !important;
}

.afiliado-container .nav-tabs .nav-link {
    color: #6c757d;
    font-weight: 600;
    border: none;
    border-bottom: 3px solid transparent;
}

.afiliado-container .nav-tabs .nav-link.active {
    color: #4a90e2;
    background-color: transparent;
    border-color: #4a90e2;
}

.afiliado-container .table th {
    background-color: #f8f9fa;
    color: #2c3e50;
    font-weight: 600;
    border-top: none;
}

.afiliado-container .badge {
    font-weight: 500;
    padding: 6px 10px;
    border-radius: 4px;
}

@media (max-width: 768px) {
    .sis-siniestro-form {
        padding: 15px;
    }
    
    .ms-panel-body {
        padding: 15px;
    }
    
    .btn {
        width: 100%;
        margin-bottom: 10px;
    }
    
    .text-end {
        text-align: left !important;
    }
    
    .afiliado-container {
        max-height: none;
        overflow-y: visible;
    }
}

.field-with-icon {
    position: relative;
}

.field-with-icon .form-control {
    padding-left: 40px;
}

.field-with-icon i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    z-index: 5;
}
CSS;

$this->registerCss($css);
?>

<div class="sis-siniestro-form">
    <?php $form = ActiveForm::begin(); ?>

    <div class="ms-panel">
        <div class="ms-panel-header">
            <h3 class="section-title">
                <i class="fas fa-file-alt text-blue-600"></i> Datos del Siniestro
            </h3>
        </div>
        <div class="ms-panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="row g-3">
                        <div class="col-md-12" style="display: none;">
                             <?= $form->field($model, 'idclinica')->textInput(['value' => $afiliado->clinica_id]) ?>
                        </div>
                        
                        <div class="col-md-6 field-with-icon">
                            <i class="fas fa-calendar-day"></i>
                            <?= $form->field($model, 'fecha')->textInput([
                                'type' => 'date',
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Seleccione la fecha',
                                'autocomplete' => 'off',
                                'value' => $model->isNewRecord ? date('Y-m-d') : Yii::$app->formatter->asDate($model->fecha, 'yyyy-MM-dd')
                            ])->label('Fecha del Siniestro') ?>
                        </div>
                        
                        <div class="col-md-6 field-with-icon">
                            <?= $form->field($model, 'hora')->textInput([
                                'type' => 'time', 
                                'class' => 'form-control form-control-lg'
                            ])->label('Hora del Siniestro') ?>
                        </div>
                        
                        <div class="col-md-12">
                        <?php
                        // 1. Primero forzamos valores de prueba
                        $selectedBaremos = []; // Valores de prueba
                        
                        // 2. Obtenemos los baremos disponibles
                        $baremosDisponibles = \yii\helpers\ArrayHelper::map(
                            \app\models\Baremo::find()
                                ->where(['clinica_id' => $afiliado->clinica_id])
                                ->andWhere(['estatus' => 'Activo'])
                                ->all(), 
                            'id', 
                            'nombre_servicio'
                        );
                        
                        // 3. Depuración detallada
                        echo "<!-- ===== INICIO DEPURACIÓN ===== -->\n";
                        echo "<!-- Modelo ID: " . $model->id . " -->\n";
                        echo "<!-- Modelo atributos: " . print_r($model->attributes, true) . " -->\n";
                        
                        // Verificar si hay baremos en el modelo
                        if (method_exists($model, 'getBaremos')) {
                            $baremosRelacion = $model->getBaremos()->all();
                            echo "<!-- Baremos desde relación (count): " . count($baremosRelacion) . " -->\n";
                            echo "<!-- Baremos desde relación: " . print_r(\yii\helpers\ArrayHelper::toArray($baremosRelacion), true) . " -->\n";
                            
                            // Si no hay baremos en la relación, intentamos con una consulta directa
                            if (empty($baremosRelacion)) {
                                $baremosDirectos = (new \yii\db\Query())
                                    ->select(['baremo_id'])
                                    ->from('sis_siniestro_baremo')
                                    ->where(['siniestro_id' => $model->id])
                                    ->column();
                                echo "<!-- Baremos desde consulta directa: " . print_r($baremosDirectos, true) . " -->\n";
                                
                                if (!empty($baremosDirectos)) {
                                    $selectedBaremos = $baremosDirectos;
                                }
                            } else {
                                $selectedBaremos = \yii\helpers\ArrayHelper::getColumn($baremosRelacion, 'id');
                            }
                        }
                        
                        echo "<!-- Baremos seleccionados (final): " . print_r($selectedBaremos, true) . " -->\n";
                        echo "<!-- Baremos disponibles (count): " . count($baremosDisponibles) . " -->\n";
                        echo "<!-- ===== FIN DEPURACIÓN ===== -->\n";
                        ?>
                        
                        <div class="field-with-icon">
                            <?= $form->field($model, 'idbaremo[]')->widget(Select2::class, [
                                'data' => $baremosDisponibles,
                                'options' => [
                                    'multiple' => true,
                                    'value' => $selectedBaremos,
                                    'placeholder' => 'Seleccione uno o más Baremos',
                                    'class' => 'form-control form-control-lg',
                                ],
                                'pluginOptions' => [
                                    'allowClear' => true,
                                    'closeOnSelect' => false,
                                    'tags' => false,
                                    'tokenSeparators' => [',', ' '],
                                ],
                                'pluginEvents' => [
                                    'select2:select' => 'function(e) { console.log("Selected:", e.params.data); }',
                                    'select2:unselect' => 'function(e) { console.log("Unselected:", e.params.data); }',
                                ]
                            ])->label('Baremos') ?>
                        </div>
                        
                        <script>
                        // Forzar la selección después de que se cargue el Select2
                        document.addEventListener('DOMContentLoaded', function() {
                            var selectedBaremos = <?= json_encode($selectedBaremos) ?>;
                            console.log('Baremos a seleccionar (JS):', selectedBaremos);
                            
                            // Esperar a que se inicialice Select2
                            var checkSelect2 = setInterval(function() {
                                var $select = $('#sis-siniestro-idbaremo');
                                if ($select.hasClass('select2-hidden-accessible')) {
                                    clearInterval(checkSelect2);
                                    console.log('Select2 inicializado, estableciendo valores...');
                                    
                                    // Establecer los valores seleccionados
                                    $select.val(selectedBaremos).trigger('change');
                                    
                                    // Verificar los valores seleccionados
                                    console.log('Valores seleccionados después de setear:', $select.val());
                                    
                                    // Forzar la actualización visual de Select2
                                    $select.trigger('select2:select');
                                }
                            }, 100);
                        });
                        </script>
                        </div>
                        
                        <div class="col-md-12">
                            <?= $form->field($model, 'atendido')->dropDownList(
                                [0 => 'No', 1 => 'Sí'],
                                [
                                    'prompt' => 'Seleccione estado', 
                                    'class' => 'form-control form-control-lg'
                                ]
                            )->label('Atendido') ?>
                        </div>
                        
                        <div class="col-md-6 field-with-icon">
                            <?= $form->field($model, 'fecha_atencion')->textInput([
                                'type' => 'date',
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Seleccione la fecha',
                                'autocomplete' => 'off',
                                'value' => $model->isNewRecord ? date('Y-m-d') : Yii::$app->formatter->asDate($model->fecha, 'yyyy-MM-dd')
                            ])->label('Fecha de Atención') ?>
                        </div>
                        
                        <div class="col-md-6 field-with-icon">
                            <?= $form->field($model, 'hora_atencion')->textInput([
                                'type' => 'time', 
                                'class' => 'form-control form-control-lg'
                            ])->label('Hora de Atención') ?>
                        </div>
                        
                        <div class="col-md-12 field-with-icon">
                            <i class="fas fa-align-left"></i>
                            <?= $form->field($model, 'descripcion')->textarea([
                                'rows' => 3, 
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Describa los detalles del siniestro...'
                            ])->label('Descripción del Siniestro') ?>
                        </div>

                        <div class="form-group text-end mt-4">
                            <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg me-2']) ?>
                            <?= Html::a('<i class="fas fa-times"></i> Cancelar', ['index', 'user_id' => $afiliado->id], ['class' => 'btn btn-warning btn-lg me-2']); ?>
                            <?php if ($model->isNewRecord): ?>
                                <?= Html::a('<i class="fas fa-eraser"></i> Limpiar', ['create', 'user_id' => $afiliado->id], ['class' => 'btn btn-outline-dark btn-lg']); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="ms-panel">
                        <div class="ms-panel-header">
                            <h3 class="section-title">
                                <i class="fas fa-user text-blue-600"></i> Datos del Afiliado
                            </h3>
                        </div>
                        <div class="ms-panel-body">
                            <div class="afiliado-container">
                                <?= $this->render('/user-datos/view', ['model' => $afiliado]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>