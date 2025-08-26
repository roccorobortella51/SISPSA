<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper; // Importar el UserHelper
use app\models\RmClinica; // Importar el modelo de la clínica

/** @var yii\web\View $this */
/** @var app\models\Planes $model */
/** @var array $itemsModels // Se asume que esta variable se pasa para el _form */
/** @var app\models\RmClinica $clinica // Se asume que el modelo de clínica se pasa a la vista */


if (!isset($clinica) && !empty($model->clinica_id)) {
    $clinica = RmClinica::findOne((int)$model->clinica_id);
    if (!$clinica) {
       
        $clinica = (object)['id' => (int)$model->clinica_id, 'nombre' => 'Clínica Desconocida'];
    }
} elseif (!isset($clinica)) {
    
    $clinica = (object)['id' => null, 'nombre' => 'Clínica Desconocida'];
}

$this->title = 'ACTUALIZAR PLÁN: ' . Html::encode($model->nombre);

$this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['/rm-clinica/index']];

if ($clinica->id !== null) { 
    $this->params['breadcrumbs'][] = ['label' => Html::encode($clinica->nombre), 'url' => ['/rm-clinica/view', 'id' => $clinica->id]];
    $this->params['breadcrumbs'][] = ['label' => 'PLANES', 'url' => ['index', 'clinica_id' => $clinica->id]];
} else {
    $this->params['breadcrumbs'][] = ['label' => 'PLANES', 'url' => ['index']];
}
$this->params['breadcrumbs'][] = ['label' => Html::encode($model->nombre), 'url' => ['view', 'id' => $model->id, 'clinica_id' => $clinica->id]];
$this->params['breadcrumbs'][] = 'Actualizar';

$rol = UserHelper::getMyRol();
$canManage = ($rol == 'superadmin'); 
?>

<div class="main-container"> 
    <div class="header-section"> 
        <h1><?= Html::encode($this->title) ?></h1>
        
        <div class="header-buttons-group"> 
            <?php if ($clinica->id !== null) : ?>
                <?= Html::a(
                    '<i class="fas fa-undo mr-2"></i> Volver', 
                    ['index', 'clinica_id' => $clinica->id], 
                    [
                        'class' => 'btn-base btn-gray', 
                        'title' => 'Volver a la lista de planes de esta clínica',
                    ]
                ) ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="ms-panel ms-panel-fh border-blue"> 
        <div class="ms-panel-header">
            <h3 class="section-title">
                <i class="fas fa-pencil-alt mr-3 text-blue-600"></i> Formulario de Actualización
            </h3>
        </div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [
                'model' => $model,
                'itemsModels' => $itemsModels,
                'clinica' => $clinica 
            ]) ?>        
        </div>
    </div>
</div>
