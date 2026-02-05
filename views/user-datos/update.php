<?php

use yii\helpers\Html;

/** @var yii\web\View $this */
/** @var app\models\UserDatos $model */

// Get the affiliate's full name and formatted cedula
$fullName = Html::encode(trim($model->nombres . ' ' . $model->apellidos));
$cedulaFormatted = Html::encode(($model->tipo_cedula ? $model->tipo_cedula . '-' : '') . $model->cedula);

// Set the title for breadcrumbs
$this->title = 'Actualizar datos del afiliado: ' . $fullName . ' | Cédula: ' . $cedulaFormatted;
$this->params['breadcrumbs'][] = ['label' => 'AFILIADOS', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Actualizar';
?>
<div class="col-xl-12 col-md-12">
    <div class="ms-panel ms-panel-fh">
        <div class="ms-panel-header d-flex justify-content-between align-items-center py-4">
            <!-- Content section takes the left side -->
            <div class="flex-grow-1 mr-4">
                <!-- Centered and larger title -->
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center justify-content-center">
                        <i class="fas fa-user-edit text-primary mr-3" style="font-size: 2.5rem;"></i>
                        <span class="text-muted display-4 font-weight-normal" style="font-size: 2.2rem; letter-spacing: 0.5px;">Actualizar datos del afiliado:</span>
                    </div>
                </div>

                <div class="d-flex align-items-center justify-content-center mt-4">
                    <div class="mr-5" style="min-width: 300px;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-user-circle mr-3 text-dark" style="font-size: 2.2rem;"></i>
                            <div>
                                <div class="text-muted small mb-1" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">NOMBRE DEL AFILIADO</div>
                                <div class="text-dark h3 font-weight-bold"><?= $fullName ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="border-left mx-4" style="height: 60px; border-color: #dee2e6 !important;"></div>

                    <div style="min-width: 250px;">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-id-card mr-3 text-dark" style="font-size: 2.2rem;"></i>
                            <div>
                                <div class="text-muted small mb-1" style="font-size: 0.85rem; font-weight: 600; text-transform: uppercase;">CÉDULA DE IDENTIDAD</div>
                                <div class="text-dark h3 font-weight-bold"><?= $cedulaFormatted ?></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Button aligned to the right -->
            <div class="flex-shrink-0 ml-auto">
                <?= Html::a(
                    '<i class="fas fa-undo mr-2"></i> Volver',
                    '#',
                    [
                        'class' => 'btn btn-primary btn-lg px-4 py-3 font-weight-bold',
                        'onclick' => 'window.history.back(); return false;',
                        'title' => 'Volver a la página anterior',
                        'style' => 'min-width: 140px; font-size: 1.1rem;'
                    ]
                ) ?>
            </div>
        </div>
        <div class="ms-panel-body">
            <?= $this->render('_form', [
                'model' => $model,
                'modelContrato' => $modelContrato,
            ]) ?>
        </div>
    </div>
</div>

<style>
    .ms-panel-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #ffffff 100%);
        border-bottom: 2px solid #e9ecef;
        padding: 2rem 1.8rem !important;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
    }

    .fa-user-edit {
        color: #0d6efd !important;
        text-shadow: 0 2px 4px rgba(13, 110, 253, 0.2);
    }

    .fa-user-circle,
    .fa-id-card {
        color: #2c3e50 !important;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
    }

    /* Centered title styling */
    .text-center.mb-4 {
        margin-bottom: 2rem !important;
    }

    .display-4 {
        font-size: 2.2rem !important;
        color: #495057 !important;
        font-weight: 400 !important;
    }

    .h3 {
        font-size: 1.75rem !important;
        line-height: 1.2;
        color: #212529 !important;
        text-shadow: 0 1px 1px rgba(0, 0, 0, 0.05);
    }

    .text-muted.small {
        letter-spacing: 0.8px;
        color: #6c757d !important;
        opacity: 0.9;
    }

    .btn-lg {
        font-weight: 600;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        transition: all 0.3s ease;
        border: none;
        border-radius: 8px;
    }

    .btn-lg:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
    }

    .border-left {
        opacity: 0.4;
    }

    /* Button container pushed to far right */
    .ml-auto {
        margin-left: auto !important;
    }

    @media (max-width: 1200px) {
        .d-flex.align-items-center.justify-content-center {
            flex-wrap: wrap;
        }

        .mr-5,
        .border-left {
            margin-right: 0 !important;
            margin-bottom: 1.5rem;
        }

        .border-left {
            display: none;
        }
    }

    @media (max-width: 992px) {
        .ms-panel-header {
            flex-direction: column;
        }

        .ml-auto {
            margin-left: 0 !important;
            margin-top: 1.5rem;
            align-self: flex-start;
            width: 100%;
        }

        .display-4 {
            font-size: 1.8rem !important;
        }

        .fa-user-edit {
            font-size: 2rem !important;
        }
    }

    @media (max-width: 768px) {
        .ms-panel-header {
            align-items: stretch !important;
            text-align: center;
        }

        .ml-auto {
            align-self: center;
        }

        .d-flex.align-items-center.justify-content-center {
            flex-direction: column;
            align-items: center !important;
        }

        .mr-5,
        div[style*="min-width: 250px"] {
            min-width: 100% !important;
            margin-right: 0 !important;
            margin-bottom: 1.5rem;
        }

        .btn-lg {
            width: 100%;
        }

        .display-4 {
            font-size: 1.6rem !important;
        }

        .fa-user-edit {
            font-size: 1.8rem !important;
            margin-right: 10px !important;
        }
    }
</style>