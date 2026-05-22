<?php
// views/sis-siniestro/audit-logs.php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap4\Modal;

$this->title = 'Auditoría de Eliminaciones';
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss("
    .audit-log-container {
        background: white;
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        padding: 20px;
    }
    
    .audit-header {
        background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 8px 8px 0 0;
        margin: -20px -20px 20px -20px;
    }
");

?>

<div class="row">
    <div class="col-md-12">
        <div class="audit-log-container">
            <div class="audit-header">
                <h3 class="mb-0">
                    <i class="fas fa-history mr-2"></i>
                    <?= Html::encode($this->title) ?>
                </h3>
                <p class="mb-0 mt-2">
                    <i class="fas fa-info-circle mr-1"></i>
                    Registro de eliminaciones de citas y atenciones médicas
                </p>
            </div>

            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'columns' => [
                    [
                        'attribute' => 'created_at',
                        'format' => 'datetime',
                        'headerOptions' => ['style' => 'width: 160px;'],
                    ],
                    [
                        'attribute' => 'user_name',
                        'label' => 'Usuario',
                        'headerOptions' => ['style' => 'width: 180px;'],
                    ],
                    [
                        'attribute' => 'user_role',
                        'label' => 'Rol',
                        'headerOptions' => ['style' => 'width: 150px;'],
                    ],
                    [
                        'attribute' => 'reason',
                        'label' => 'Motivo',
                        'format' => 'ntext',
                        'contentOptions' => ['style' => 'max-width: 300px;'],
                    ],
                    [
                        'attribute' => 'deleted_data',
                        'label' => 'Datos Eliminados',
                        'format' => 'raw',
                        'value' => function ($model) {
                            $data = json_decode($model->deleted_data, true);
                            if ($data) {
                                $esCita = isset($data['es_cita']) ? $data['es_cita'] : 0;
                                $tipo = $esCita == 1 ? 'Cita' : 'Atención';

                                $details = '<strong>' . $tipo . '</strong><br>';
                                $details .= '<small>';
                                $details .= 'Fecha: ' . (isset($data['fecha']) ? $data['fecha'] : 'N/A') . '<br>';
                                $details .= 'Costo: $' . number_format(isset($data['costo_total']) ? $data['costo_total'] : 0, 2);

                                if (isset($data['baremos']) && count($data['baremos']) > 0) {
                                    $details .= '<br>Servicios: ' . count($data['baremos']);
                                }
                                $details .= '</small>';

                                return $details;
                            }
                            return 'N/A';
                        },
                        'headerOptions' => ['style' => 'width: 200px;'],
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{view}',
                        'buttons' => [
                            'view' => function ($url, $model) {
                                return Html::a('<i class="fas fa-eye"></i>', '#', [
                                    'title' => 'Ver detalles completos',
                                    'class' => 'btn btn-sm btn-info',
                                    'onclick' => "showAuditDetails('" . $model->id . "'); return false;",
                                ]);
                            },
                        ],
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>

<?php
// Add modal for viewing details
Modal::begin([
    'id' => 'audit-details-modal',
    'title' => '<i class="fas fa-file-alt mr-2"></i> Detalles Completos',
    'size' => 'modal-lg',
]);
Modal::end();

$js = <<<JS
function showAuditDetails(id) {
    $.ajax({
        url: 'audit-details?id=' + id,
        type: 'get',
        success: function(response) {
            $('#audit-details-modal .modal-body').html(response);
            $('#audit-details-modal').modal('show');
        }
    });
}
JS;
$this->registerJs($js);
?>