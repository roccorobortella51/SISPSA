<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\SisSiniestro */

$this->title = 'Detalles de la Atención '.$afiliado->nombres." ".$afiliado->apellidos." ".$afiliado->tipo_cedula."-".$afiliado->cedula; // Este sigue siendo el título para la página y breadcrumbs
$this->params['breadcrumbs'][] = ['label' => 'Siniestros', 'url' => ['index', 'user_id' => $model->iduser]];
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
 <!-- Carga de Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
<div class="sis-siniestro-view">

    <div class="card">
       
        <div class="card-body">
                <div class="flex flex-col justify-start items-start mb-6 p-4 bg-white rounded-lg shadow-sm">
                    <h1 class="text-3xl font-bold text-gray-800 mb-4"><?= Html::encode($this->title) ?></h1>
                    <div class="flex flex-wrap gap-3 w-full justify-start">
                        <?= Html::a(
                            '<i class="fas fa-edit mr-2"></i> Actualizar',
                            ['update', 'id' => $model->id],
                            ['class' => 'px-5 py-2 bg-blue-600 text-white rounded-lg shadow-md hover:bg-blue-700 transition duration-300 ease-in-out flex items-center text-sm font-medium']
                        ) ?>
                        <?= Html::a(
                            '<i class="fas fa-trash-alt mr-2"></i> Eliminar',
                            ['delete', 'id' => $model->id],
                            [
                                'class' => 'px-5 py-2 bg-red-600 text-white rounded-lg shadow-md hover:bg-red-700 transition duration-300 ease-in-out flex items-center text-sm font-medium',
                                'data' => [
                                    'confirm' => '¿Está seguro de que desea eliminar esta clínica?',
                                    'method' => 'post',
                                ],
                            ]
                        ) ?>
                        <?= Html::a(
                            '<i class="fas fa-undo mr-2"></i> Volver',
                            '#',
                            [
                                'class' => 'px-5 py-2 bg-gray-200 text-gray-800 rounded-lg shadow-md hover:bg-gray-300 transition duration-300 ease-in-out flex items-center text-sm font-medium',
                                'onclick' => 'window.history.back(); return false;',
                                'title' => 'Volver a la página anterior',
                            ]
                        ) ?>
                    </div>
                </div>

            <?= DetailView::widget([
                'model' => $model,
                'attributes' => [
                    // 'id',
                    [
                        'attribute' => 'idclinica',
                        'value' => $model->clinica->nombre,
                        'label' => 'Clínica',
                    ],
                    'fecha',
                    'hora',
                    [
                        'attribute' => 'idbaremo',
                        'value' => $model->baremo->nombre_servicio,
                        'label' => 'Baremo',
                    ],
                    [
                        'attribute' => 'atendido',
                        'value' => $model->atendido ? 'Sí' : 'No',
                    ],
                    'fecha_atencion',
                    'hora_atencion',
                    /*[
                        'attribute' => 'iduser',
                        'value' => $model->afiliado->nombres." ".$model->afiliado->apellidos, 
                        'label' => 'Usuario',
                    ],**/
                    'descripcion:ntext',
                    'created_at:datetime',
                    'updated_at:datetime',
                ],
            ]) ?>
        </div>
    </div>
</div>