<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\SisSiniestro $model */
/** @var app\models\UserDatos $afiliado */
/** @var int $es_cita */  // Add this line

// Get es_cita parameter from URL if not passed from controller
if (!isset($es_cita)) {
    $esCita = (int)Yii::$app->request->get('es_cita', 0);
} else {
    $esCita = (int)$es_cita;
}

$termino = $esCita === 1 ? 'Cita' : 'Atención';

$this->title = 'Crear ' . $termino . ' Médica: ' . Html::encode($afiliado->nombres . " " . $afiliado->apellidos . " " . $afiliado->tipo_cedula . "-" . $afiliado->cedula);
$this->params['breadcrumbs'][] = ['label' => 'Afiliados', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
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
                'es_cita' => $esCita,  // ADD THIS LINE - Pass es_cita to _form
                'user_id' => $model->iduser ?? Yii::$app->request->get('user_id'),
            ]) ?>
        </div>
    </div>
</div>