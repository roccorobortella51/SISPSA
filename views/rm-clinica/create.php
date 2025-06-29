<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\RmClinica $model */
/** @var array $listaEstados // Estas variables se pasan al _form_create.php */
/** @var array $listaEstatus // Estas variables se pasan al _form_create.php */
/** @var string $mode // Para indicar el modo 'create' */
/** @var bool $isNewRecord // Siempre true para la creación */

// Configuración del título de la página y los breadcrumbs
$this->title = 'CREAR NUEVA CLINICA'; // Título específico para la acción de crear
$this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<div class="rm-clinica-create">
    <div class="row justify-content-center mb-4">
        <div class="col-12">
            <div class="mb-3">
                <?= Html::a(
                    '<i class="fa fa-arrow-left"></i>',
                    ['index'],
                    ['class' => 'btn btn-secondary btn-sm', 'title' => 'Volver a la lista']
                ) ?>
            </div>
           
        </div>
    </div>
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white py-3">
            <h6 class="m-0 font-weight-bold text-white text-center">CREAR NUEVA CLÍNICA</h6>
        </div> <div class="card-body">
            <?php foreach (Yii::$app->session->getAllFlashes() as $type => $message): ?>
                <?php if (in_array($type, ['success', 'danger', 'warning'])): ?>
                    <div class="alert alert-<?= $type ?> alert-dismissible fade show mt-3" role="alert">
                        <?= Html::encode($message) ?>
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>

            <br>

            
                    <?= $this->render('_form_create', [
                        'model' => $model,
                        'listaEstados' => $listaEstados,
                        'listaEstatus' => $listaEstatus,
                        'mode' => $mode,
                        'isNewRecord' => $isNewRecord,
                    ]) ?>
                    </div> </div> </div> </div> </div> 

