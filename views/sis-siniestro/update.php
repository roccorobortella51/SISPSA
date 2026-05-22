<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\SisSiniestro $model */
/** @var app\models\UserDatos $afiliado */
/** @var int $es_cita */

// Get es_cita parameter from URL (fallback to passed variable)
$esCita = isset($es_cita) ? (int)$es_cita : (int)Yii::$app->request->get('es_cita', 0);
$termino = $esCita === 1 ? 'Cita' : 'Atención';

$this->title = 'Actualizar datos de la ' . $termino . ': ' . $afiliado->nombres . " " . $afiliado->apellidos . " " . $afiliado->tipo_cedula . "-" . $afiliado->cedula;
$this->params['breadcrumbs'][] = ['label' => 'AFILIADOS', 'url' => ['index', 'user_id' => $afiliado->id]];
$this->params['breadcrumbs'][] = 'Actualizar';

// Debug: Check if nombre_doctor is loaded (remove after fixing)
if (!empty($model->nombre_doctor)) {
}
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
                'es_cita' => $esCita,
            ]) ?>
        </div>
    </div>
</div>