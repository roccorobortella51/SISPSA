<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\components\UserHelper; 
use app\models\RmClinica; 

/** @var yii\web\View $this */
/** @var app\models\Baremo $model */ 

$clinica = null;
if (!empty($model->clinica_id)) {
    $clinica = RmClinica::findOne($model->clinica_id);
    if (!$clinica) {
        $clinica = (object)['id' => $model->clinica_id, 'nombre' => 'Clínica Desconocida'];
    }
} else {
    $clinica = (object)['id' => null, 'nombre' => 'Clínica Desconocida'];
}

$this->title = 'ACTUALIZAR BAREMO: ' . Html::encode($model->nombre_servicio);

$this->params['breadcrumbs'][] = ['label' => 'CLÍNICAS', 'url' => ['/rm-clinica/index']];

if ($clinica->id !== null) { 
    $this->params['breadcrumbs'][] = ['label' => Html::encode($clinica->nombre), 'url' => ['/rm-clinica/view', 'id' => $clinica->id]];
    $this->params['breadcrumbs'][] = ['label' => 'BAREMOS', 'url' => ['index', 'clinica_id' => $clinica->id]];
} else {
    $this->params['breadcrumbs'][] = ['label' => 'BAREMOS', 'url' => ['index']];
}
$this->params['breadcrumbs'][] = ['label' => Html::encode($model->nombre_servicio), 'url' => ['index', 'clinica_id' => $clinica->id]];
$this->params['breadcrumbs'][] = 'Actualizar';

$rol = UserHelper::getMyRol();
$canManage = ($rol == 'superadmin');
?>

<div class="main-container"> 
   
    <!-- Encabezado y Botones de Acción Principal -->
    <div class="header-section"> 
        <h1><?= Html::encode($this->title) ?></h1>
        
        <div class="header-buttons-group"> 
            <?php if ($clinica->id !== null) : ?>
                <?= Html::a(
                    '<i class="fas fa-undo mr-2"></i> Volver',
                    ['index', 'clinica_id' => $clinica->id], 
                    [
                        'class' => 'btn-base btn-gray', 
                        'title' => 'Volver a la lista de baremos de esta clínica',
                    ]
                ) ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Panel que contiene el formulario de actualización -->
    <div class="ms-panel ms-panel-fh border-blue"> 
        <div class="ms-panel-header">
            <h3 class="section-title">
                <i class="fas fa-pencil-alt mr-3 text-blue-600"></i> Formulario de Actualización
            </h3>
        </div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [
                'model' => $model,
                'clinica_id' => $clinica->id, 
            ]) ?>        
        </div>
    </div>
</div>
