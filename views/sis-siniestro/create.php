<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\SisSiniestro $model */
/** @var app\models\UserDatos $afiliado */
/** @var int $es_cita */

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
            <div>
                <?php
                // Determine icon based on es_cita (SAME PATTERN AS index.php)
                $isCitaMode = ($esCita === 1);
                $iconClass = $isCitaMode ? 'fa-calendar-alt' : 'fa-heartbeat';
                $iconColor = $isCitaMode ? '#28a745' : '#dc3545';
                ?>
                <h1 style="font-size: 1.8rem; font-weight: 700; letter-spacing: -0.3px; margin: 0; display: flex; align-items: center;">
                    <i class="fas <?= $iconClass ?>" style="color: <?= $iconColor ?>; font-size: 4rem; margin-right: 12px; line-height: 1;"></i>
                    <span style="line-height: 1.4;"><?= Html::encode($this->title); ?></span>
                </h1>
            </div>
            <div>
                <?= Html::a(
                    '<i class="fas fa-undo"></i> Volver',
                    '#',
                    [
                        'class' => 'btn btn-primary btn-lg',
                        'onclick' => 'window.history.back(); return false;',
                        'title' => 'Volver a la página anterior',
                        'style' => 'font-size: 1rem; padding: 12px 24px; border-radius: 12px;'
                    ]
                ) ?>
            </div>
        </div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [
                'model' => $model,
                'afiliado' => $afiliado,
                'es_cita' => $esCita,
                'user_id' => $model->iduser ?? Yii::$app->request->get('user_id'),
            ]) ?>
        </div>
    </div>
</div>