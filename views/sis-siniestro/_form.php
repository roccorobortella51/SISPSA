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
                        
                        <div class="col-md-6">
                            <?= $form->field($model, 'fecha')->textInput([ // Cambiado a input type="date"
                                'type' => 'date',
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Seleccione la fecha',
                                'autocomplete' => 'off',
                                'value' => $model->isNewRecord ? date('Y-m-d') : Yii::$app->formatter->asDate($model->fecha, 'yyyy-MM-dd')
                            ])->label('Fecha del Siniestro') ?>
                        </div>
                        
                        <div class="col-md-6">
                            <?= $form->field($model, 'hora')->textInput(['type' => 'time', 'class' => 'form-control form-control-lg'])->label('Hora del Siniestro') ?>
                        </div>
                        
                        <div class="col-md-12">
                        <?php
                        // 1. Primero forzamos valores de prueba
                        $selectedBaremos = [1, 2]; // Valores de prueba
                        
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
                        <?= $form->field($model, 'idbaremo[]')->widget(Select2::class, [
                            'data' => $baremosDisponibles,
                            'options' => [
                                'multiple' => true,
                                'value' => $selectedBaremos, // Mover value aquí
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
                                ['prompt' => 'Seleccione estado', 'class' => 'form-control form-control-lg']
                            )->label('Atendido') ?>
                        </div>
                        
                        <div class="col-md-6">
                            <?= $form->field($model, 'fecha_atencion')->textInput([ // Cambiado a input type="date"
                                'type' => 'date',
                                'class' => 'form-control form-control-lg',
                                'placeholder' => 'Seleccione la fecha',
                                'autocomplete' => 'off',
                                'value' => $model->isNewRecord ? date('Y-m-d') : Yii::$app->formatter->asDate($model->fecha, 'yyyy-MM-dd')
                            ])->label('Fecha de atencion') ?>
                        </div>
                        
                        <div class="col-md-6">
                            <?= $form->field($model, 'hora_atencion')->textInput(['type' => 'time', 'class' => 'form-control form-control-lg'])->label('Hora de Atención') ?>
                        </div>
                        
                        <div class="col-md-12">
                            <?= $form->field($model, 'descripcion')->textarea(['rows' => 3, 'class' => 'form-control form-control-lg'])->label('Descripción del Siniestro') ?>
                        </div>

                        <div class="form-group text-end mt-4">
                <?= Html::submitButton('<i class="fas fa-save"></i> Guardar', ['class' => 'btn btn-success btn-lg me-2']) ?>
                <?= Html::a('Cancelar', ['index', 'user_id' => $afiliado->id], ['class' => 'btn btn-warning btn-lg me-2']); ?>
                <?php if ($model->isNewRecord): ?>
                    <?= Html::a('Limpiar', ['create', 'user_id' => $afiliado->id], ['class' => 'btn btn-outline-dark btn-lg']); ?>
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
                            <?php echo $this->render('/user-datos/view', ['model' => $afiliado]); ?>
                        </div>
                    </div>
                </div>
            </div>
            
          
        </div>
    </div>
    <?php ActiveForm::end(); ?>
</div>
