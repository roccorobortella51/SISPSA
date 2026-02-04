<?php

use yii\helpers\Html;
use yii\web\View;

/** @var View $this */
/** @var app\models\Agente $model */

$this->title = 'ACTUALIZAR AGENTE: ' . $model->nom;
$this->params['breadcrumbs'][] = ['label' => 'AGENCIAS', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'ACTUALIZAR';

// Añadimos un bloque de estilo específico para asegurar el tamaño del botón
$this->registerCss("
    .btn-agencia-asesores {
        font-size: 1.1rem !important;
        font-weight: bold !important;
        padding: 0.8rem 1.6rem !important;
    }
");
?>

<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
       <div class="ms-panel-header">
            <div class="d-flex justify-content-between align-items-center w-100">
                <h3>
                    <?= Html::encode('ACTUALIZAR AGENCIA'); ?> 
                    <span class="text-primary">#<?= $model->id ?></span>
                </h3>
                <?php if (!$model->isNewRecord) { ?>
                    <?= Html::a(
                        '<i class="fas fa-users mr-2"></i> AGENTES DE ESTA AGENCIA',
                        ['agente-fuerza/index-by-agente', 'agente_id' => $model->id],
                        [
                            'class' => 'btn btn-success btn-agencia-asesores',
                        ]
                    ) ?>
                <?php } ?>
            </div>
        </div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [ 
                'model' => $model,
                'isNewRecord' => false,
            ]) ?>        
        </div>
    </div>
</div>