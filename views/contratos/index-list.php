<?php

use app\models\Contratos;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\LinkPager;

/** @var yii\web\View $this */
/** @var app\models\UserDatos $afiliado */
/** @var app\models\ContratosSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Contratos del Afiliado';
$contracts = $dataProvider->getModels();
$hasMultipleContracts = $hasMultipleContracts ?? false;
$today = date('Y-m-d');
$canCancelContracts = Yii::$app->user->can('superadmin')
    || Yii::$app->user->can('GERENTE-COMERCIALIZACION')
    || Yii::$app->user->can('GERENTE-CLINICA')
    || Yii::$app->user->can('GERENTE-OPERACIONES');
$statusClasses = [
    'Activo' => 'badge-success',
    'Registrado' => 'badge-info',
    'Anulado' => 'badge-danger',
    'Vencido' => 'badge-warning',
    'Pendiente' => 'badge-primary',
    'Suspendido' => 'badge-secondary',
    'suspendido' => 'badge-secondary',
    'Creado Manual' => 'badge-secondary',
];
?>

<div class="container-fluid contract-list-page">
    <div class="contract-list-heading d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div class="mb-2 mb-md-0">
            <h1 class="h3 mb-1 text-white">
                <i class="fas fa-file-contract mr-2"></i><?= Html::encode($this->title) ?>
            </h1>
            <div class="contract-list-affiliate">
                <?= Html::encode(trim($afiliado->nombres . ' ' . $afiliado->apellidos)) ?>
                <span class="mx-1">|</span>
                <?= Html::encode(($afiliado->tipo_cedula ? $afiliado->tipo_cedula . '-' : '') . ($afiliado->cedula ?? 'N/A')) ?>
            </div>
        </div>
        <?= Html::a(
            '<i class="fas fa-arrow-left mr-1"></i> Volver al afiliado',
            ['/user-datos/view', 'id' => $afiliado->id],
            ['class' => 'btn btn-outline-light btn-sm']
        ) ?>
    </div>

    <div class="contract-list-toolbar p-3 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <h2 class="h5 mb-0 contract-list-section-title">Lista de contratos</h2>
            <span class="badge badge-primary px-2 py-2">
                <?= (int)$dataProvider->getTotalCount() ?> <?= $dataProvider->getTotalCount() === 1 ? 'contrato' : 'contratos' ?>
            </span>
        </div>

        <form method="get" action="<?= Html::encode(Url::to(['/contratos/index-list', 'user_id' => $afiliado->id])) ?>">
            <div class="form-row align-items-end">
                <div class="form-group col-md-4 mb-2">
                    <label for="contract-number-filter" class="small font-weight-bold d-block text-center">Número de contrato</label>
                    <?= Html::activeTextInput($searchModel, 'nrocontrato', [
                        'id' => 'contract-number-filter',
                        'class' => 'form-control form-control-sm text-center',
                        'placeholder' => 'Buscar por número',
                    ]) ?>
                </div>
                <div class="form-group col-md-4 mb-2">
                    <label for="contract-status-filter" class="small font-weight-bold d-block text-center">Estatus</label>
                    <?= Html::activeDropDownList($searchModel, 'estatus', Contratos::getStatusOptions(), [
                        'id' => 'contract-status-filter',
                        'class' => 'form-control form-control-sm text-center',
                        'prompt' => 'Todos los estatus',
                    ]) ?>
                </div>
                <div class="form-group col-md-4 mb-2 text-center">
                    <button type="submit" class="btn btn-primary btn-sm mr-1">
                        <i class="fas fa-search mr-1"></i> Buscar
                    </button>
                    <?= Html::a(
                        '<i class="fas fa-undo mr-1"></i> Limpiar',
                        ['/contratos/index-list', 'user_id' => $afiliado->id],
                        ['class' => 'btn btn-outline-secondary btn-sm']
                    ) ?>
                </div>
            </div>
        </form>
    </div>

    <?php if ($contracts): ?>
        <div class="table-responsive contract-list-table-wrap">
            <table class="table mb-0">
                <thead class="contract-list-table-head">
                    <tr>
                        <th scope="col">N°</th>
                        <th scope="col">Contrato</th>
                        <th scope="col">Plan</th>
                        <th scope="col">Vigencia</th>
                        <th scope="col" class="text-right">Monto</th>
                        <th scope="col">Estatus</th>
                        <th scope="col" class="text-center">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($contracts as $index => $contract): ?>
                        <?php
                        $rowOffset = $dataProvider->pagination
                            ? $dataProvider->pagination->getOffset()
                            : 0;
                        $rowNumber = $dataProvider->getTotalCount() - $rowOffset - $index;
                        $status = $contract->estatus ?: 'Registrado';
                        $statusClass = $statusClasses[$status] ?? 'badge-secondary';
                        $canRenew = $status !== Contratos::STATUS_ANULADO
                            && !empty($contract->fecha_ven)
                            && $contract->fecha_ven < $today
                            && !$hasMultipleContracts;
                        $printUrl = Url::to([
                            '/user-datos/generar-contratov',
                            'id' => $afiliado->id,
                            'contract_id' => $contract->id,
                        ]);
                        ?>
                        <tr class="contract-list-row">
                            <td class="text-center text-muted font-weight-bold"><?= $rowNumber ?></td>
                            <td>
                                <span class="font-weight-bold text-dark">
                                    <?= Html::encode($contract->nrocontrato ?: 'Contrato #' . $contract->id) ?>
                                </span>
                                <small class="d-block text-muted">ID <?= (int)$contract->id ?></small>
                            </td>
                            <td><?= Html::encode($contract->plan ? $contract->plan->nombre : 'Sin plan') ?></td>
                            <td>
                                <?= $contract->fecha_ini ? Yii::$app->formatter->asDate($contract->fecha_ini, 'php:d/m/Y') : 'N/A' ?>
                                <span class="text-muted mx-1">a</span>
                                <?= $contract->fecha_ven ? Yii::$app->formatter->asDate($contract->fecha_ven, 'php:d/m/Y') : 'Indefinido' ?>
                            </td>
                            <td class="text-center">
                                <?= $contract->monto !== null ? Yii::$app->formatter->asCurrency($contract->monto, $contract->moneda ?: 'USD') : 'N/A' ?>
                            </td>
                            <td><span class="badge <?= $statusClass ?>"><?= Html::encode($status) ?></span></td>
                            <td class="text-center text-nowrap">
                                <?= Html::a('<i class="fas fa-eye"></i>', ['/contratos/view', 'id' => $contract->id], [
                                    'class' => 'btn btn-outline-primary btn-sm',
                                    'title' => 'Ver contrato',
                                    'aria-label' => 'Ver contrato',
                                ]) ?>
                                <?= Html::a('<i class="fas fa-print"></i>', $printUrl, [
                                    'class' => 'btn btn-outline-secondary btn-sm ml-1',
                                    'title' => 'Imprimir este contrato',
                                    'aria-label' => 'Imprimir este contrato',
                                    'target' => '_blank',
                                    'rel' => 'noopener',
                                ]) ?>
                                <?php if ($canCancelContracts && $status !== Contratos::STATUS_ANULADO): ?>
                                    <?= Html::a('<i class="fas fa-ban"></i><span class="sr-only">Anular contrato</span>', ['/contratos/anular-form', 'id' => $contract->id, 'return_to_index_list' => 1], [
                                        'class' => 'btn btn-outline-danger btn-sm ml-1',
                                        'title' => 'Anular contrato',
                                        'aria-label' => 'Anular contrato',
                                    ]) ?>
                                <?php endif; ?>
                                <?php if ($canRenew): ?>
                                    <?= Html::a('<i class="fas fa-sync-alt"></i><span class="sr-only">Generar nuevo contrato</span>', ['/contratos/renew', 'id' => $contract->id], [
                                        'class' => 'btn btn-outline-info btn-sm ml-1',
                                        'title' => 'Generar Nuevo Contrato',
                                        'aria-label' => 'Generar Nuevo Contrato',
                                    ]) ?>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="mt-3">
            <?= LinkPager::widget([
                'pagination' => $dataProvider->pagination,
                'options' => ['class' => 'pagination justify-content-center'],
                'linkContainerOptions' => ['class' => 'page-item'],
                'linkOptions' => ['class' => 'page-link'],
                'activePageCssClass' => 'active',
                'disabledPageCssClass' => 'disabled',
                'prevPageLabel' => '&laquo;',
                'nextPageLabel' => '&raquo;',
                'firstPageLabel' => '&laquo;&laquo;',
                'lastPageLabel' => '&raquo;&raquo;',
            ]) ?>
        </div>
    <?php else: ?>
        <div class="alert alert-light border text-center py-5" role="status">
            <i class="fas fa-file-contract fa-2x text-muted mb-3"></i>
            <h3 class="h5 text-dark">No hay contratos para mostrar</h3>
            <p class="text-muted mb-0">No se encontraron contratos con los filtros seleccionados.</p>
        </div>
    <?php endif; ?>
</div>

<?php
$this->registerCss(<<<CSS
.contract-list-page { color: #201f1e; }
.contract-list-heading {
    padding: 1.25rem 1.5rem;
    border-radius: 4px;
    background: linear-gradient(120deg, #0078d4 0%, #005a9e 100%);
    box-shadow: 0 2px 8px rgba(0, 90, 158, 0.2);
}
.contract-list-affiliate { color: rgba(255, 255, 255, 0.86); }
.contract-list-heading .btn-outline-light { border-color: rgba(255, 255, 255, 0.75); }
.contract-list-heading .btn-outline-light:hover { color: #005a9e; }
.contract-list-toolbar,
.contract-list-table-wrap {
    background: #fff;
    border: 1px solid #edebe9;
    border-radius: 4px;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
}
.contract-list-table-wrap { overflow: hidden; }
.contract-list-section-title {
    color: #fff;
    background: #0078d4;
    padding: 0.65rem 0.9rem;
    border-radius: 3px;
}
.contract-list-table-head th {
    border-top: 0;
    border-bottom: 1px solid #005a9e;
    background: #0078d4;
    color: #fff !important;
    font-size: 0.82rem;
    white-space: nowrap;
    text-align: center;
}
.contract-list-table-wrap tbody td { vertical-align: middle; text-align: center; }
.contract-list-table-wrap tbody tr:nth-child(even) { background: #f3f8fc; }
.contract-list-table-wrap tbody tr:nth-child(odd) { background: #fff; }
.contract-list-table-wrap tbody tr:hover { background: #e6f2fb; }
.contract-list-page .badge { font-weight: 600; }
@media (max-width: 767.98px) {
    .contract-list-page { padding-left: 10px; padding-right: 10px; }
    .contract-list-table-wrap { overflow-x: auto; }
    .contract-list-table-wrap table { min-width: 900px; }
}
CSS);
?>