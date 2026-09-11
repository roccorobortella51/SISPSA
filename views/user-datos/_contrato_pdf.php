<?php

use yii\helpers\Html;

/**
 * @var array $data
 * @var string $logo
 * @var string $firmas
 */
?>
<div class="pdf-container">

    <!-- ============================================ -->
    <!-- PERFECTLY BALANCED HEADER                    -->
    <!-- Logo: Left | Company Info: Center | Type: Right -->
    <!-- ============================================ -->
    <table style="width: 100%; margin-bottom: 20px; border: none;">
        <tr>
            <!-- LEFT: LOGO -->
            <td style="width: 25%; vertical-align: middle; border: none;">
                <div style="text-align: left;">
                    <img src="<?= Html::encode($logo) ?>" alt="SISPSA" style="width: 130px; height: auto; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.15);">
                </div>
            </td>

            <!-- CENTER: COMPANY INFORMATION -->
            <td style="width: 50%; vertical-align: middle; text-align: center; border: none;">
                <div style="font-size: 20px; font-weight: bold; color: #1a3a6b; letter-spacing: 1.5px;">SISTEMA INTEGRAL DE SALUD PROGRAMADO</div>
                <div style="font-size: 15px; font-weight: 600; color: #2c5f8a; margin-top: 5px;">Medicina Prepagada, S.A.</div>
                <div style="font-size: 9px; color: #555; margin-top: 12px; line-height: 1.5;">
                    Inscrita en la Superintendencia de la Actividad Aseguradora bajo el No. MP000013<br>
                    R.I.F.: J-50654922
                </div>
            </td>

            <!-- RIGHT: AFFILIATION TYPE -->
            <td style="width: 25%; vertical-align: middle; text-align: right; border: none;">
                <div style="background-color: #f0f4f8; border: 1px solid #d0d7de; border-radius: 10px; padding: 10px 15px; display: inline-block; text-align: left;">
                    <div style="font-size: 8px; font-weight: bold; color: #666; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">TIPO DE AFILIACIÓN</div>
                    <div style="display: flex; gap: 20px;">
                        <span style="margin-right: 10px; font-size: 9px;">
                            <span style="font-size: 14px;"><?= ($data['affiliation_type_id'] ?? 1) == 1 ? '☒' : '☐' ?></span>
                            INDIVIDUAL
                        </span>
                        <span style="margin-right: 10px; font-size: 9px;">
                            <span style="font-size: 14px;"><?= ($data['affiliation_type_id'] ?? 1) == 2 ? '☒' : '☐' ?></span>
                            COLECTIVO
                        </span>
                    </div>
                </div>
            </td>
        </tr>
    </table>

    <!-- ============================================ -->
    <!-- SECTION 1: DATOS DEL CONTRATO                -->
    <!-- ============================================ -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <thead>
            <tr>
                <th colspan="8" style="background-color: #1a3a6b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">DATOS DEL CONTRATO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>CONTRATO N°:</strong> <?= Html::encode($data['contract_number'] ?? 'PENDIENTE') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>FECHA EMISIÓN:</strong> <?= $data['fecha_emision'] ?? date('d/m/Y') ?>
                </td>
                <td colspan="4" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>TOTAL CUOTAS DE AFILIACIÓN:</strong> <?= $data['total_cuotas'] ?? 12 ?> cuotas
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px; background-color: #f9f9f9;">
                    <strong>VIGENCIA DESDE:</strong>
                    <?php
                    $startDate = $data['contract_start_date'] ?? null;
                    echo $startDate ? Yii::$app->formatter->asDate($startDate, 'dd/MM/yyyy') : 'N/A';
                    ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px; background-color: #f9f9f9;">
                    <strong>VIGENCIA HASTA:</strong>
                    <?php
                    $endDate = $data['contract_end_date'] ?? null;
                    echo $endDate ? Yii::$app->formatter->asDate($endDate, 'dd/MM/yyyy') : 'N/A';
                    ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px; background-color: #f9f9f9;">
                    <strong>MONTO MENSUAL:</strong> <?= Yii::$app->formatter->asCurrency($data['monthly_amount'] ?? 0, 'USD') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px; background-color: #f9f9f9;">
                    <strong>PLAN CONTRATADO:</strong> <?= Html::encode($data['plan_selected'] ?? 'N/A') ?>
                </td>
            </tr>
            <tr>
                <td colspan="4" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>LÍMITE DE COBERTURA:</strong> <?= $data['plan_coverage_limit'] ?? 'N/A' ?>
                </td>
                <td colspan="4" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>SUCURSAL:</strong> <?= Html::encode($data['clinica_name'] ?? 'No asignada') ?>
                </td>
            </tr>
            <tr>
                <td colspan="8" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>INTERMEDIARIO:</strong> <?= Html::encode($data['intermediary_name'] ?? 'N/A') ?>
                    <br><strong>CÓDIGO:</strong> <?= Html::encode($data['intermediary_code'] ?? 'N/A') ?>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- SPACE BETWEEN SECTION 1 AND SECTION 2 -->
    <div style="margin-top: 25px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 2: DATOS DEL CONTRATANTE             -->
    <!-- ============================================ -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <thead>
            <tr>
                <th colspan="8" style="background-color: #1a3a6b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">DATOS DEL CONTRATANTE</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>NOMBRES Y APELLIDOS:</strong>
                    <?= Html::encode($data['contratante_nombre'] ?? 'N/A') ?> <?= Html::encode($data['contratante_apellido'] ?? '') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>C.I. / R.I.F.:</strong> <?= Html::encode($data['contratante_ci'] ?? 'N/A') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>NACIONALIDAD:</strong> <?= Html::encode($data['contratante_nacionalidad'] ?? 'N/A') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>ESTADO CIVIL:</strong> <?= Html::encode($data['contratante_estado_civil'] ?? 'N/A') ?>
                </td>
            </tr>
            <tr>
                <td colspan="1" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>LUGAR NACIMIENTO:</strong> <?= Html::encode($data['contratante_lugar_nacimiento'] ?? 'N/A') ?>
                </td>
                <td colspan="1" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>FECHA NACIMIENTO:</strong>
                    <?= isset($data['contratante_fecha_nac']) && $data['contratante_fecha_nac'] ? date('d/m/Y', strtotime($data['contratante_fecha_nac'])) : 'N/A' ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>SEXO:</strong> <?= Html::encode($data['contratante_sexo'] ?? 'N/A') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>PROFESIÓN:</strong> <?= Html::encode($data['contratante_profesion'] ?? 'N/A') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>OCUPACIÓN:</strong> <?= Html::encode($data['contratante_ocupacion'] ?? 'N/A') ?>
                </td>
            </tr>

            <!-- ROW: Actividad Económica -->
            <tr>
                <td colspan="4" style="border: 1px solid #000; padding: 4px; vertical-align: top; font-size: 10px;">
                    <strong>ACTIVIDAD ECONÓMICA:</strong><br>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['contratante_actividad_economica'] ?? '') == 'Industrial' ? '☑' : '☐' ?></span> Industrial</span>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['contratante_actividad_economica'] ?? '') == 'Comercial' ? '☑' : '☐' ?></span> Comercial</span>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['contratante_actividad_economica'] ?? '') == 'Profesional' ? '☑' : '☐' ?></span> Profesional</span>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['contratante_actividad_economica'] ?? '') == 'Gubernamental' ? '☑' : '☐' ?></span> Gubernamental</span>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: top; font-size: 10px;">
                    <strong>DESCRIPCIÓN ACTIVIDAD:</strong><br>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['contratante_descripcion_actividad'] ?? '') == 'Independiente' ? '☑' : '☐' ?></span> Independiente</span>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['contratante_descripcion_actividad'] ?? '') == 'Dependiente' ? '☑' : '☐' ?></span> Dependiente</span>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['contratante_descripcion_actividad'] ?? '') == 'Societaria' ? '☑' : '☐' ?></span> Societaria</span>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: top; font-size: 10px;">
                    <strong>INGRESO ANUAL Bs:</strong><br>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['contratante_ingreso_anual'] ?? '') == 'De 1 a 5 Salarios mínimos' ? '☑' : '☐' ?></span> De 1 a 5</span>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['contratante_ingreso_anual'] ?? '') == 'De 6 a 10 Salarios mínimos' ? '☑' : '☐' ?></span> De 6 a 10</span>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['contratante_ingreso_anual'] ?? '') == 'De 11 a 20 Salarios mínimos' ? '☑' : '☐' ?></span> De 11 a 20</span>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['contratante_ingreso_anual'] ?? '') == 'De 20 Salarios mínimos en adelante' ? '☑' : '☐' ?></span> 20+</span>
                </td>
            </tr>
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>DIRECCIÓN DE RESIDENCIA:</strong> <?= Html::encode($data['contratante_direccion_residencia'] ?? 'N/A') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>TELÉFONO RESIDENCIA:</strong> <?= Html::encode($data['contratante_telefono_residencia'] ?? 'N/A') ?>
                </td>
            </tr>
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>DIRECCIÓN DE OFICINA:</strong> <?= Html::encode($data['contratante_direccion_oficina'] ?? 'N/A') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>TELÉFONO OFICINA:</strong> <?= Html::encode($data['contratante_telefono_oficina'] ?? 'N/A') ?>
                </td>
            </tr>
            <tr>
                <td colspan="8" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>DIRECCIÓN DE COBRO:</strong> <?= Html::encode($data['contratante_direccion_cobro'] ?? 'N/A') ?>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>TELÉFONO CELULAR:</strong> <?= Html::encode($data['contratante_telefono_celular'] ?? 'N/A') ?>
                </td>
                <td colspan="5" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>CORREO ELECTRÓNICO:</strong> <?= Html::encode($data['contratante_email'] ?? 'N/A') ?>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- SPACE BETWEEN SECTION 2 AND SECTION 3 -->
    <div style="margin-top: 25px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 3: DATOS DEL PROPUESTO AFILIADO TITULAR -->
    <!-- ============================================ -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <thead>
            <tr>
                <th colspan="8" style="background-color: #1a3a6b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">DATOS DEL PROPUESTO AFILIADO TITULAR</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>NOMBRES Y APELLIDOS:</strong> <?= $data['proposed_affiliate_name'] ?? 'N/A' ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>C.I. / R.I.F.:</strong> <?= $data['proposed_affiliate_ci'] ?? 'N/A' ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>NACIONALIDAD:</strong> <?= $data['proposed_affiliate_nationality'] ?? 'N/A' ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>ESTADO CIVIL:</strong> <?= $data['proposed_affiliate_marital_status'] ?? 'N/A' ?>
                </td>
            </tr>
            <tr>
                <td colspan="1" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>LUGAR NACIMIENTO:</strong> <?= $data['proposed_affiliate_birthplace'] ?? 'N/A' ?>
                </td>
                <td colspan="1" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>FECHA NACIMIENTO:</strong>
                    <?= isset($data['proposed_affiliate_birthdate']) && $data['proposed_affiliate_birthdate'] ? date('d/m/Y', strtotime($data['proposed_affiliate_birthdate'])) : 'N/A' ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>SEXO:</strong> <?= $data['proposed_affiliate_sex'] ?? 'N/A' ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>PROFESIÓN:</strong> <?= $data['proposed_affiliate_profession'] ?? 'N/A' ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>OCUPACIÓN:</strong> <?= $data['proposed_affiliate_occupation'] ?? 'N/A' ?>
                </td>
            </tr>
            <tr>
                <td colspan="4" style="border: 1px solid #000; padding: 4px; vertical-align: top; font-size: 10px;">
                    <strong>ACTIVIDAD ECONÓMICA:</strong><br>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['proposed_affiliate_economic_activity'] ?? '') == 'Industrial' ? '☑' : '☐' ?></span> Industrial</span>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['proposed_affiliate_economic_activity'] ?? '') == 'Comercial' ? '☑' : '☐' ?></span> Comercial</span>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['proposed_affiliate_economic_activity'] ?? '') == 'Profesional' ? '☑' : '☐' ?></span> Profesional</span>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['proposed_affiliate_economic_activity'] ?? '') == 'Gubernamental' ? '☑' : '☐' ?></span> Gubernamental</span>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: top; font-size: 10px;">
                    <strong>DESCRIPCIÓN ACTIVIDAD:</strong><br>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['proposed_affiliate_activity_description'] ?? '') == 'Independiente' ? '☑' : '☐' ?></span> Independiente</span>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['proposed_affiliate_activity_description'] ?? '') == 'Dependiente' ? '☑' : '☐' ?></span> Dependiente</span>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['proposed_affiliate_activity_description'] ?? '') == 'Societaria' ? '☑' : '☐' ?></span> Societaria</span>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: top; font-size: 10px;">
                    <strong>INGRESO ANUAL Bs:</strong><br>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['proposed_affiliate_annual_income'] ?? '') == 'De 1 a 5 Salarios mínimos' ? '☑' : '☐' ?></span> De 1 a 5</span>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['proposed_affiliate_annual_income'] ?? '') == 'De 6 a 10 Salarios mínimos' ? '☑' : '☐' ?></span> De 6 a 10</span>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['proposed_affiliate_annual_income'] ?? '') == 'De 11 a 20 Salarios mínimos' ? '☑' : '☐' ?></span> De 11 a 20</span>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['proposed_affiliate_annual_income'] ?? '') == 'De 20 Salarios mínimos en adelante' ? '☑' : '☐' ?></span> 20+</span>
                </td>
            </tr>
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>DIRECCIÓN DE RESIDENCIA:</strong> <?= $data['proposed_affiliate_residence_address'] ?? 'N/A' ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>TELÉFONO RESIDENCIA:</strong> <?= $data['proposed_affiliate_phone_residence'] ?? 'N/A' ?>
                </td>
            </tr>
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>DIRECCIÓN DE OFICINA:</strong> <?= $data['proposed_affiliate_office_address'] ?? 'N/A' ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>TELÉFONO OFICINA:</strong> <?= $data['proposed_affiliate_phone_office'] ?? 'N/A' ?>
                </td>
            </tr>
            <tr>
                <td colspan="8" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>DIRECCIÓN DE COBRO:</strong> <?= $data['proposed_affiliate_billing_address'] ?? 'N/A' ?>
                </td>
            </tr>
            <tr>
                <td colspan="3" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>TELÉFONO CELULAR:</strong> <?= $data['proposed_affiliate_cell_phone'] ?? 'N/A' ?>
                </td>
                <td colspan="5" style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 10px;">
                    <strong>CORREO ELECTRÓNICO:</strong> <?= $data['proposed_affiliate_email'] ?? 'N/A' ?>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- SPACE BETWEEN SECTIONS -->
    <div style="margin-top: 25px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 4: SERVICIOS INCLUIDOS               -->
    <!-- ============================================ -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <thead>
            <tr>
                <th colspan="8" style="background-color: #1a3a6b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">
                    SERVICIOS INCLUIDOS EN EL PLAN: <?= Html::encode($data['plan_selected'] ?? 'N/A') ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th width="40%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">DESCRIPCIÓN DE SERVICIOS</th>
                <th width="20%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">PLAZO DE ESPERA (P/E)</th>
                <th width="40%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">DESCRIPCIÓN</th>
            </tr>
            <?php if (!empty($data['plan_services'])): ?>
                <?php foreach ($data['plan_services'] as $service): ?>
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 9px;"><?= Html::encode($service['servicio'] ?? '') ?></td>
                        <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 9px; text-align: center;"><?= Html::encode($service['espera'] ?? 'NO APLICA') ?></td>
                        <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; font-size: 9px;"><?= Html::encode($service['descripcion'] ?? 'Servicio incluido en el plan') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center; font-size: 9px; color: #888;">No hay servicios específicos registrados para este plan.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- SPACE BETWEEN SECTIONS -->
    <div style="margin-top: 15px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 5: DE LAS EXCLUSIONES                -->
    <!-- ============================================ -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <thead>
            <tr>
                <th colspan="8" style="background-color: #1a3a6b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">DE LAS EXCLUSIONES</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="8" style="border: 1px solid #000; padding: 8px; vertical-align: middle; font-size: 9px; line-height: 1.5;">
                    <?= nl2br(Html::encode($data['exclusiones'] ?? 'Las exclusiones generales aplicables a todos los planes de medicina prepagada según las condiciones generales del contrato.')) ?>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- SPACE BETWEEN SECTIONS -->
    <div style="margin-top: 25px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 6: COBERTURA OPCIONAL                -->
    <!-- ============================================ -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <thead>
            <tr>
                <th colspan="8" style="background-color: #1a3a6b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">COBERTURA OPCIONAL (El Contratante no está obligado a contratar esta cobertura)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th colspan="2" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">COBERTURA</th>
                <th colspan="2" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">DEDUCIBLE</th>
                <th colspan="2" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">LÍMITE DE COBERTURA</th>
                <th colspan="2" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">SELECCIÓN</th>
            </tr>
            <tr>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center;">SERVICIO DE MATERNIDAD</td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center;"><?= $data['maternity_deductible'] ?? 'NO APLICA' ?></td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center;"><?= $data['maternity_coverage_limit'] ?? 'NO APLICA' ?></td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center; font-size: 18px;">
                    <?= ($data['maternity_coverage'] ?? false) ? '☑' : '☐' ?>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- SPACE BETWEEN SECTIONS -->
    <div style="margin-top: 25px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 7: GRUPO FAMILIAR                   -->
    <!-- ============================================ -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <thead>
            <tr>
                <th colspan="5" style="background-color: #1a3a6b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">GRUPO FAMILIAR: (PERSONAS A ASEGURAR, ADEMÁS DEL AFILIADO TITULAR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th width="30%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">APELLIDOS Y NOMBRES</th>
                <th width="20%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">CÉDULA DE IDENTIDAD</th>
                <th width="20%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">PARENTESCO</th>
                <th width="10%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">SEXO</th>
                <th width="20%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">F/NACIMIENTO</th>
            </tr>
            <?php if (!empty($data['family_group'])): ?>
                <?php foreach ($data['family_group'] as $member): ?>
                    <tr>
                        <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center; font-size: 9px;"><?= Html::encode($member['name'] ?? '') ?></td>
                        <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center; font-size: 9px;"><?= Html::encode($member['ci'] ?? '') ?></td>
                        <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center; font-size: 9px;"><?= Html::encode($member['relationship'] ?? '') ?></td>
                        <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center; font-size: 9px;"><?= Html::encode($member['sex'] ?? '') ?></td>
                        <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center; font-size: 9px;"><?= isset($member['birthdate']) && $member['birthdate'] ? date('d/m/Y', strtotime($member['birthdate'])) : '' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php for ($i = count($data['family_group'] ?? []); $i < 5; $i++): ?>
                <tr>
                    <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center;">&nbsp;</td>
                    <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center;">&nbsp;</td>
                    <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center;">&nbsp;</td>
                    <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center;">&nbsp;</td>
                    <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center;">&nbsp;</td>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <!-- SPACE BETWEEN SECTIONS -->
    <div style="margin-top: 25px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 8: BENEFICIARIO                     -->
    <!-- ============================================ -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <thead>
            <tr>
                <th colspan="5" style="background-color: #1a3a6b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">BENEFICIARIO EN CASO DE MUERTE DEL PROPUESTO AFILIADO TITULAR</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th width="30%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">APELLIDOS Y NOMBRES</th>
                <th width="20%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">CÉDULA DE IDENTIDAD</th>
                <th width="20%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">PARENTESCO</th>
                <th width="10%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">SEXO</th>
                <th width="20%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">F/NACIMIENTO</th>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center; font-weight: bold;"><?= Html::encode($data['beneficiary_name'] ?? 'N/A') ?></td>
                <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center;"><?= Html::encode($data['beneficiary_ci'] ?? 'N/A') ?></td>
                <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center;"><?= Html::encode($data['beneficiary_relationship'] ?? 'N/A') ?></td>
                <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center;"><?= Html::encode($data['beneficiary_sex'] ?? 'N/A') ?></td>
                <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center;"><?= isset($data['beneficiary_birthdate']) && $data['beneficiary_birthdate'] ? date('d/m/Y', strtotime($data['beneficiary_birthdate'])) : 'N/A' ?></td>
            </tr>
            <tr>
                <td colspan="5" style="border: 1px solid #000; padding: 6px; vertical-align: middle; font-size: 9px; background-color: #f9f9f9; text-align: center;">
                    <strong>NOTA:</strong> En caso de muerte de algún otro AFILIADO en este contrato, el BENEFICIARIO es el AFILIADO TITULAR.
                </td>
            </tr>
        </tbody>
    </table>

    <!-- SPACE BETWEEN SECTIONS -->
    <div style="margin-top: 25px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 9: DATOS BANCARIOS                  -->
    <!-- ============================================ -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <thead>
            <tr>
                <th colspan="8" style="background-color: #1a3a6b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">DATOS BANCARIOS DEL CONTRATANTE</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th width="25%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">NOMBRE DEL TITULAR</th>
                <th width="20%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">C.I.</th>
                <th width="25%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">NRO. CUENTA/TARJETA</th>
                <th width="30%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">BANCO</th>
            </tr>
            <tr>
                <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center; font-weight: bold;"><?= Html::encode($data['bank_account_holder_name'] ?? 'N/A') ?></td>
                <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center;"><?= Html::encode($data['bank_account_ci'] ?? 'N/A') ?></td>
                <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center;"><?= Html::encode($data['bank_account_number'] ?? 'N/A') ?></td>
                <td style="border: 1px solid #000; padding: 4px; vertical-align: middle; text-align: center;"><?= Html::encode($data['bank_name'] ?? 'N/A') ?></td>
            </tr>
            <tr>
                <td colspan="4" style="border: 1px solid #000; padding: 8px; vertical-align: middle;">
                    <strong>TIPO DE CUENTA:</strong>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['bank_account_type'] ?? '') == 'Cuenta Corriente' ? '☑' : '☐' ?></span> Cuenta Corriente</span>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['bank_account_type'] ?? '') == 'Cuenta Ahorro' ? '☑' : '☐' ?></span> Cuenta Ahorro</span>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['bank_account_type'] ?? '') == 'Tarjeta Crédito Visa' ? '☑' : '☐' ?></span> Tarjeta Crédito Visa</span>
                    <span style="margin-right: 10px; font-size: 9px;"><span style="font-size: 14px;"><?= ($data['bank_account_type'] ?? '') == 'Tarjeta Crédito MasterCard' ? '☑' : '☐' ?></span> Tarjeta Crédito MasterCard</span>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- SPACE BETWEEN SECTIONS -->
    <div style="margin-top: 25px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 10: DECLARACIONES Y AUTORIZACIONES   -->
    <!-- ============================================ -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <thead>
            <tr>
                <th colspan="6" style="background-color: #1a3a6b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">DECLARACIONES Y AUTORIZACIONES</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 10px; vertical-align: middle; font-size: 9px; line-height: 1.6;">
                    <p>Yo, <span style="font-weight: bold; text-decoration: underline;"><?= $data['declaration_proposed_affiliate_name'] ?? 'N/A' ?></span>, titular de la cédula de identidad N° <span style="font-weight: bold; text-decoration: underline;"><?= $data['declaration_proposed_affiliate_ci'] ?? 'N/A' ?></span> en mi carácter de PROPUESTO AFILIADO TITULAR,</p>
                    <p>* Declaro que he leído cuidadosamente y totalmente, una a una, todas las preguntas y respuestas consignadas en esta solicitud de seguro y que ellas son verdaderas, amplias, completas y exactas.</p>
                    <p>* Declaro que el correo electrónico suministrado me pertenece e identifica plenamente, por lo que autorizo expresamente a Sistema Integral de Salud Programado Medicina Prepagada, S.A. para enviarme todos los documentos que forman parte del contrato y cualquier comunicación pertinente por este medio.</p>

                    <?php if (!empty(trim($data['declaration_contracting_party_name'] ?? ''))): ?>
                        <p>Yo, <span style="font-weight: bold; text-decoration: underline;"><?= $data['declaration_contracting_party_name'] ?? 'N/A' ?></span>, titular de la cédula de identidad N° <span style="font-weight: bold; text-decoration: underline;"><?= $data['declaration_contracting_party_ci'] ?? 'N/A' ?></span> en mi carácter de CONTRATANTE,</p>
                        <p>* Doy fe de que el dinero utilizado para el pago de las cuotas, provienen de una fuente lícita y su origen no guarda relación alguna con capitales, bienes, haberes, valores, títulos u operaciones, producto de actividades ilícitas o que provenga de los delitos de Delincuencia Organizada u otras conductas tipificadas en la legislación venezolana.</p>
                        <p>* Autorizo a Sistema Integral de Salud Programado Medicina Prepagada, S.A. a debitar de la Cuenta Bancaria / o cargar en la Tarjeta de Crédito, cuyos datos proporciono en esta solicitud, los cobros de cuotas de este servicio de medicina prepagada durante su vigencia a partir de su emisión. Y me comprometo a mantener el monto suficiente para cumplir con la obligación del pago de la cuota correspondiente.</p>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 0;">
                    <img src="<?= Html::encode($firmas) ?>" alt="firmas" style="width: 100%;">
                </td>
            </tr>
            <tr>
                <td colspan="6" style="background-color: #1a3a6b; padding: 8px; border: 1px solid #000; text-align: center;">
                    <h4 style="margin: 0; font-weight: bold; color: white; text-align: center;">INTERMEDIARIO DE LA ACTIVIDAD ASEGURADORA</h4>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border: 1px solid #000; padding: 8px; vertical-align: middle; font-weight: bold; background-color: #e8e8e8;">NOMBRE Y APELLIDO:</td>
                <td colspan="2" style="border: 1px solid #000; padding: 8px; vertical-align: middle; font-weight: bold; background-color: #e8e8e8;">CÓDIGO №:</td>
                <td colspan="2" style="border: 1px solid #000; padding: 8px; vertical-align: middle; font-weight: bold; background-color: #e8e8e8;">C.I. / R.I.F. / PASAPORTE:</td>
            </tr>
            <tr>
                <td colspan="2" style="border: 1px solid #000; padding: 8px; vertical-align: middle; border-bottom: 1px solid #000;">
                    <?= Html::encode($data['intermediary_name'] ?? '') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 8px; vertical-align: middle; border-bottom: 1px solid #000;">
                    <?= Html::encode($data['intermediary_code'] ?? '') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 8px; vertical-align: middle; border-bottom: 1px solid #000;">
                    <?= Html::encode($data['intermediary_ci'] ?? '') ?>
                </td>
            </tr>
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 8px; vertical-align: middle; text-align: center; background-color: #f0f4f8;">
                    <p style="font-size: 8px; margin: 0;">Aprobado por la Superintendencia de la Actividad Aseguradora según Providencia Nº SAA-SUT-34169 de fecha 13/02/2025</p>
                </td>
            </tr>
        </tbody>
    </table>

</div>