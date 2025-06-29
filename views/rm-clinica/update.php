<?php

use yii\helpers\Html;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var app\models\RmClinica $model */
/** @var int $tab // Este es el índice de la pestaña activa, pasado desde el controlador */
/** @var array $listaEstados // Necesario para el _form de la pestaña 0 */
/** @var array $listaEstatus // Necesario para el _form de la pestaña 0 */

// Configuración del título de la página y los breadcrumbs
$this->title = 'Clínica: ' . Html::encode($model->nombre);
$this->params['breadcrumbs'][] = ['label' => 'CLINÍCAS', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

// Aseguramos que $tab sea un entero y por defecto sea 0 si no se ha establecido
$tab = (int) ($tab ?? 0);
$mode = 'edit'; // La vista 'update' siempre estará en modo de edición para los datos principales

?>

<div class="rm-clinica-update">

    <div>
        <?= Html::a(
            '<i class="fa fa-arrow-left"></i>',
            ['index'],
            ['class' => 'btn btn-secondary btn-sm ml-3', 'title' => 'Volver a la lista']
        ) ?>
    </div>
    <br>

    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="m-0 font-weight-bold text-center flex-grow-1">
                    <?= Html::encode($this->title) ?>
                </h4>
            </div>
        </div>

        <div class="card-body"> <div class="d-flex flex-wrap align-items-center gap-3 px-3 py-2">
                <?= Html::a(
                    'Datos de la Clínica',
                    ['update', 'id' => $model->id, 'tab' => 0],
                    ['class' => 'btn btn-outline-primary ' . ($tab == 0 ? 'active' : '')]
                ) ?>

                <?= Html::a(
                    'Baremo',
                    ['update', 'id' => $model->id, 'tab' => 1],
                    ['class' => 'btn btn-outline-primary ' . ($tab == 1 ? 'active' : '')]
                ) ?>
                <?= Html::a(
                    'Planes',
                    ['update', 'id' => $model->id, 'tab' => 2],
                    ['class' => 'btn btn-outline-primary ' . ($tab == 2 ? 'active' : '')]
                ) ?>
                <?= Html::a(
                    'Usuarios',
                    ['update', 'id' => $model->id, 'tab' => 3],
                    ['class' => 'btn btn-outline-primary ' . ($tab == 3 ? 'active' : '')]
                ) ?>
                <?= Html::a(
                    'Médicos',
                    ['update', 'id' => $model->id, 'tab' => 4],
                    ['class' => 'btn btn-outline-primary ' . ($tab == 4 ? 'active' : '')]
                ) ?>
                <?= Html::a(
                    'Niveles de Alertas',
                    ['update', 'id' => $model->id, 'tab' => 5],
                    ['class' => 'btn btn-outline-primary ' . ($tab == 5 ? 'active' : '')]
                ) ?>
            </div>

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

            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 font-weight-bold text-white text-center">DATOS</h6>
                </div>
                <div class="card-body">
                    <?php
                    switch ($tab) {
                        case 0: // Pestaña: Datos de la Clínica (tu formulario existente)
                            echo $this->render('_form', [
                                'model' => $model,
                                'listaEstados' => $listaEstados,
                                'listaEstatus' => $listaEstatus,
                                'mode' => $mode,
                                'isNewRecord' => $model->isNewRecord,
                            ]);
                            break;
                        case 1: // Pestaña: Baremo
                            echo $this->render('_baremo_tab', ['model' => $model]);
                            break;
                        case 2: // Pestaña: Planes
                            echo $this->render('_planes_tab', ['model' => $model]);
                            break;
                        case 3: // Pestaña: Usuarios
                            echo $this->render('_usuarios_tab', ['model' => $model]);
                            break;
                        case 4: // Pestaña: Médicos
                            echo $this->render('_medicos_tab', ['model' => $model]);
                            break;
                        case 5: // Pestaña: Niveles de Alertas
                            echo $this->render('_alertas_tab', ['model' => $model]);
                            break;
                        default:
                            echo "<h3>Pestaña no encontrada o inválida.</h3>";
                            break;
                    }
                    ?>
                </div>
            </div>
        </div> </div> </div> ```



