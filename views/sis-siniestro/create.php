<?php

use yii\helpers\Html;


/** @var yii\web\View $this */
/** @var app\models\SisSiniestro $model */
/** @var app\models\UserDatos $afiliado */

$this->title = 'Crear Atención: ' . Html::encode($afiliado->nombres . " " . $afiliado->apellidos . " " . $afiliado->tipo_cedula . "-" . $afiliado->cedula);
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
            ]) ?>        
        </div>
    </div>
</div>
