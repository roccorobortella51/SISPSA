<?php

use yii\helpers\Html;
use yii\bootstrap4\Alert;

/** @var yii\web\View $this */
/** @var app\models\AgenteFuerza $model */ // Correct model for AgenteFuerza
/* @var app\models\Agente $agente */ // Make sure $agente is passed from the controller

// Set the page title for updating an AgenteFuerza
$this->title = 'CREAR AGENCIA';
$this->params['breadcrumbs'][] = ['label' => 'AGENCIAS', 'url' => ['index']]; // Link to the main index for AgenteFuerza
$this->params['breadcrumbs'][] = 'CREAR AGENCIA';
?>

<div class="agente-fuerza-update">

    <div class="col-xl-12 col-md-12">
        <div class="ms-panel ms-panel-fh">
            <div class="ms-panel-header d-flex justify-content-between align-items-center mb-3">
                <h1 class="m-0"><?= Html::encode($this->title); ?></h1>

                <div>
                    <?= Html::a('<i class="fas fa-undo"></i> Volver', ['index'], ['class' => 'btn btn-primary btn-lg']) ?>
                </div>
            </div>

            <?php
            // INICIO: Código para mostrar mensajes flash de Yii
            // Esto recorrerá todos los mensajes flash que se hayan establecido en la sesión
            // (ej. 'success', 'error', 'warning', 'info')
            foreach (Yii::$app->session->getAllFlashes() as $type => $message) {
                // Solo muestra los tipos de alerta que Bootstrap reconoce
                if (in_array($type, ['success', 'danger', 'warning', 'info'])) {
                    echo Alert::widget([
                        'options' => [
                            'class' => 'alert-' . $type, 
                        ],
                        'body' => $message, // El contenido del mensaje flash
                    ]);
                }
            }
            // FIN: Código para mostrar mensajes flash de Yii
            ?>


            <div class="ms-panel-body">
                <?= $this->render('_form', [
                    'model' => $model,
                    'isNewRecord' => $isNewRecord,
                    
                ]) ?>
            </div>
        </div>
    </div>

</div>