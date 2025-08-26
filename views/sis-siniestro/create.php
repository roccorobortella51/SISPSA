<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm; // ¡IMPORTANTE! Asegúrate de que esta línea esté presente

/** @var yii\web\View $this */
/** @var app\models\SisSiniestro $model */
/** @var app\models\UserDatos $afiliado */

$this->title = 'Crear Atención: ' . Html::encode($afiliado->nombres . " " . $afiliado->apellidos . " " . $afiliado->tipo_cedula . "-" . $afiliado->cedula);
$this->params['breadcrumbs'][] = ['label' => 'Afiliados', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="sis-siniestro-create">
    <?php 
    // Inicia el formulario aquí. Esto hace que la variable $form esté disponible
    // para todos los campos de formulario dentro de este bloque.
    $form = ActiveForm::begin(); 
    ?>
    <div class="card">
        <div class="ms-panel-header d-flex justify-content-between align-items-center">
            <h1><?= Html::encode($this->title); ?></h1>
            <div>
                <?= Html::a(
                    '<i class="fas fa-undo"></i> Volver', 
                    '#',
                    [
                        'class' => 'btn btn-primary btn-lg', 
                        'onclick' => 'window.history.back(); return false;', 
                        'title' => 'Volver a la página anterior', 
                    ]
                ) ?> 
            </div>
        </div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [
                'model' => $model,
                'afiliado' => $afiliado,
                'form' => $form, // Pasa la instancia de $form al parcial _form.php
            ]) ?>        
        </div>
    </div>
</div>

