<?php

use yii\helpers\Html;

/**
 * @var array  $data
 * @var string $logo
 * @var string $firmas
 */
?>
<div class="pdf-container">

    <!-- ============================================ -->
    <!-- HEADER: Logo | Company Info | Type            -->
    <!-- ============================================ -->
    <table style="width: 100%; margin-bottom: 20px; border: none;">
        <tr>
            <td style="width: 25%; vertical-align: middle; border: none;">
                <div style="text-align: left;">
                    <img src="<?= Html::encode($logo) ?>" alt="SISPSA"
                        style="width: 130px; height: auto; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.15);">
                </div>
            </td>
            <td style="width: 50%; vertical-align: middle; text-align: center; border: none;">
                <div style="font-size: 20px; font-weight: bold; color: #1a3a6b; letter-spacing: 1.5px;">
                    SISTEMA INTEGRAL DE SALUD PROGRAMADO
                </div>
                <div style="font-size: 15px; font-weight: 600; color: #2c5f8a; margin-top: 5px;">
                    Medicina Prepagada, S.A.
                </div>
                <div style="font-size: 9px; color: #555; margin-top: 12px; line-height: 1.5;">
                    Inscrita en la Superintendencia de la Actividad Aseguradora bajo el No. MP000013<br>
                    R.I.F.: J-50654922
                </div>
            </td>
            <td style="width: 25%; vertical-align: middle; text-align: right; border: none;">
                <div style="background-color: #f0f4f8; border: 1px solid #d0d7de; border-radius: 10px; padding: 10px 15px; display: inline-block; text-align: left;">
                    <div style="font-size: 8px; font-weight: bold; color: #666; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">
                        TIPO DE AFILIACIÓN
                    </div>
                    <div style="display: flex; gap: 20px;">
                        <span style="margin-right: 10px; font-size: 9px;">
                            <span style="font-size: 14px;">☐</span> INDIVIDUAL
                        </span>
                        <span style="margin-right: 10px; font-size: 9px;">
                            <span style="font-size: 14px;">☒</span> COLECTIVO
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
                <th colspan="8" style="background-color: #1a3a6b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">
                    DATOS DEL CONTRATO COLECTIVO
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>CONTRATO N°:</strong> <?= Html::encode($data['contract_number'] ?? 'PENDIENTE') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>FECHA EMISIÓN:</strong> <?= $data['fecha_emision'] ?? date('d/m/Y') ?>
                </td>
                <td colspan="4" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>TOTAL AFILIADOS CUBIERTOS:</strong> <?= (int)($data['total_empleados'] ?? 0) ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; font-size: 10px; background-color: #f9f9f9;">
                    <strong>PLANES INCLUIDOS:</strong> <?= (int)($data['total_plan_count'] ?? 0) ?>
                </td>
                <td colspan="3" style="border: 1px solid #000; padding: 4px; font-size: 10px; background-color: #f9f9f9;">
                    <strong>VIGENCIA:</strong>
                    <?= $data['contract_start_date'] ? date('d/m/Y', strtotime($data['contract_start_date'])) : 'N/A' ?>
                    &rarr;
                    <?= $data['contract_end_date'] ? date('d/m/Y', strtotime($data['contract_end_date'])) : 'N/A' ?>
                </td>
                <td colspan="3" style="border: 1px solid #000; padding: 4px; font-size: 10px; background-color: #f9f9f9;">
                    <strong>TOTAL MENSUAL:</strong>
                    $<?= number_format((float)($data['grand_subtotal'] ?? 0), 2, ',', '.') ?>
                </td>
            </tr>
            <tr>
                <td colspan="8" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>INTERMEDIARIO:</strong> <?= Html::encode($data['intermediary_name'] ?? 'N/A') ?>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    <strong>CÓDIGO:</strong> <?= Html::encode($data['intermediary_code'] ?? 'N/A') ?>
                    &nbsp;&nbsp;|&nbsp;&nbsp;
                    <strong>C.I.:</strong> <?= Html::encode($data['intermediary_ci'] ?? 'N/A') ?>
                </td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 25px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 2: DATOS DEL CONTRATANTE              -->
    <!-- ============================================ -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <thead>
            <tr>
                <th colspan="8" style="background-color: #1a3a6b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">
                    DATOS DEL CONTRATANTE (PERSONA JURÍDICA)
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="4" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>RAZÓN SOCIAL:</strong> <?= Html::encode($data['corporate_name'] ?? 'N/A') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>R.I.F.:</strong> <?= Html::encode($data['corporate_rif'] ?? 'N/A') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>TELÉFONO:</strong> <?= Html::encode($data['corporate_phone'] ?? 'N/A') ?>
                </td>
            </tr>
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>DOMICILIO FISCAL:</strong> <?= Html::encode($data['corporate_domicilio_fiscal'] ?? 'N/A') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>EMAIL:</strong> <?= Html::encode($data['corporate_email'] ?? 'N/A') ?>
                </td>
            </tr>
            <tr>
                <td colspan="8" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>DIRECCIÓN:</strong> <?= Html::encode($data['corporate_address'] ?? 'N/A') ?>
                </td>
            </tr>
            <tr>
                <td colspan="4" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>LUGAR DE REGISTRO:</strong> <?= Html::encode($data['corporate_lugar_registro'] ?? 'N/A') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>TOMO:</strong> <?= Html::encode($data['corporate_tomo'] ?? 'N/A') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>FOLIO:</strong> <?= Html::encode($data['corporate_folio'] ?? 'N/A') ?>
                </td>
            </tr>
            <tr>
                <td colspan="4" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>FECHA REGISTRO MERCANTIL:</strong>
                    <?= !empty($data['corporate_fecha_registro']) ? date('d/m/Y', strtotime($data['corporate_fecha_registro'])) : 'N/A' ?>
                </td>
                <td colspan="4" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>ACTIVIDAD ECONÓMICA:</strong> <?= Html::encode($data['corporate_economic_activity'] ?? 'N/A') ?>
                </td>
            </tr>
            <tr>
                <td colspan="8" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>PRODUCTOS / SERVICIOS:</strong> <?= Html::encode($data['corporate_products_services'] ?? 'N/A') ?>
                </td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 25px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 3: REPRESENTANTE LEGAL                -->
    <!-- ============================================ -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <thead>
            <tr>
                <th colspan="8" style="background-color: #1a3a6b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">
                    REPRESENTANTE LEGAL DEL CONTRATANTE
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="4" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>NOMBRES Y APELLIDOS:</strong> <?= Html::encode($data['corporate_legal_representative_name'] ?? 'N/A') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>C.I.:</strong> <?= Html::encode($data['corporate_legal_representative_ci'] ?? 'N/A') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>NACIONALIDAD:</strong> <?= Html::encode($data['corporate_legal_representative_nationality'] ?? 'N/A') ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>ESTADO CIVIL:</strong> <?= Html::encode($data['corporate_legal_representative_marital_status'] ?? 'N/A') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>PROFESIÓN:</strong> <?= Html::encode($data['corporate_legal_representative_profession'] ?? 'N/A') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>OCUPACIÓN:</strong> <?= Html::encode($data['corporate_legal_representative_occupation'] ?? 'N/A') ?>
                </td>
                <td colspan="2" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>TELÉFONO:</strong> <?= Html::encode($data['corporate_legal_representative_phone'] ?? 'N/A') ?>
                </td>
            </tr>
            <tr>
                <td colspan="8" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                    <strong>DIRECCIÓN:</strong> <?= Html::encode($data['corporate_legal_representative_address'] ?? 'N/A') ?>
                </td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 25px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 4: PLANES Y AFILIADOS CUBIERTOS      -->
    <!-- One sub-table per plan                        -->
    <!-- ============================================ -->
    <?php foreach (($data['plan_sections'] ?? []) as $index => $section): ?>
        <?php $sectionNumber = $index + 1; ?>

        <!-- Plan header -->
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
            <thead>
                <tr>
                    <th colspan="7" style="background-color: #1a3a6b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 6px; font-size: 11px;">
                        PLAN <?= $sectionNumber ?> DE <?= (int)($data['total_plan_count'] ?? 0) ?>:
                        <?= Html::encode($section['plan_name'] ?? 'N/A') ?>
                        &mdash;
                        <?= (int)($section['count'] ?? 0) ?> AFILIADO(S)
                    </th>
                </tr>
            </thead>
        </table>

        <!-- Plan attributes -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
            <tbody>
                <tr>
                    <td colspan="2" style="border: 1px solid #000; padding: 4px; font-size: 10px; background-color: #f9f9f9;">
                        <strong>MONEDA:</strong> <?= Html::encode($section['plan_currency'] ?? 'USD') ?>
                    </td>
                    <td colspan="2" style="border: 1px solid #000; padding: 4px; font-size: 10px; background-color: #f9f9f9;">
                        <strong>LÍMITE DE COBERTURA:</strong> <?= Html::encode($section['plan_coverage_limit'] ?? 'N/A') ?>
                    </td>
                    <td colspan="3" style="border: 1px solid #000; padding: 4px; font-size: 10px; background-color: #f9f9f9;">
                        <strong>DEDUCIBLE:</strong> <?= Html::encode($section['plan_deductible'] ?? 'NO APLICA') ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                        <strong>PRECIO POR AFILIADO:</strong> $<?= number_format((float)($section['precio_plan'] ?? 0), 2, ',', '.') ?>
                    </td>
                    <td colspan="4" style="border: 1px solid #000; padding: 4px; font-size: 10px;">
                        <strong>SUBTOTAL MENSUAL:</strong> $<?= number_format((float)($section['subtotal'] ?? 0), 2, ',', '.') ?>
                    </td>
                </tr>
            </tbody>
        </table>

        <!-- Affiliates table -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
            <thead>
                <tr>
                    <th width="5%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">#</th>
                    <th width="35%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">APELLIDOS Y NOMBRES</th>
                    <th width="15%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">CÉDULA DE IDENTIDAD</th>
                    <th width="12%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">F/NACIMIENTO</th>
                    <th width="10%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">SEXO</th>
                    <th width="13%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">TELÉFONO</th>
                    <th width="10%" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">PRECIO</th>
                </tr>
                <?php if (!empty($section['empleados'])): ?>
                    <?php foreach ($section['empleados'] as $i => $emp): ?>
                        <tr>
                            <td style="border: 1px solid #000; padding: 4px; text-align: center; font-size: 9px;"><?= $i + 1 ?></td>
                            <td style="border: 1px solid #000; padding: 4px; font-size: 9px;"><?= Html::encode($emp['nombre_completo'] ?? '') ?></td>
                            <td style="border: 1px solid #000; padding: 4px; text-align: center; font-size: 9px;"><?= Html::encode($emp['cedula'] ?? '') ?></td>
                            <td style="border: 1px solid #000; padding: 4px; text-align: center; font-size: 9px;">
                                <?= !empty($emp['fechanac']) ? date('d/m/Y', strtotime($emp['fechanac'])) : 'N/A' ?>
                            </td>
                            <td style="border: 1px solid #000; padding: 4px; text-align: center; font-size: 9px;"><?= Html::encode($emp['sexo'] ?? 'N/A') ?></td>
                            <td style="border: 1px solid #000; padding: 4px; text-align: center; font-size: 9px;"><?= Html::encode($emp['telefono'] ?? 'N/A') ?></td>
                            <td style="border: 1px solid #000; padding: 4px; text-align: right; font-size: 9px;">$<?= number_format((float)($emp['precio'] ?? 0), 2, ',', '.') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="border: 1px solid #000; padding: 4px; text-align: center; font-size: 9px; color: #888;">
                            No hay afiliados registrados para este plan.
                        </td>
                    </tr>
                <?php endif; ?>
                <tr>
                    <td colspan="6" style="background-color: #e8e8e8; border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; font-size: 10px;">
                        SUBTOTAL <?= Html::encode($section['plan_name'] ?? '') ?>:
                    </td>
                    <td style="background-color: #e8e8e8; border: 1px solid #000; padding: 6px; text-align: right; font-weight: bold; font-size: 10px;">
                        $<?= number_format((float)($section['subtotal'] ?? 0), 2, ',', '.') ?>
                    </td>
                </tr>
                </tbody>
        </table>

        <!-- Plan services -->
        <?php if (!empty($section['plan_services'])): ?>
            <table style="width: 100%; border-collapse: collapse; margin-top: 8px; margin-bottom: 5px;">
                <thead>
                    <tr>
                        <th colspan="3" style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px; font-size: 10px;">
                            SERVICIOS INCLUIDOS EN EL PLAN: <?= Html::encode($section['plan_name'] ?? 'N/A') ?>
                        </th>
                    </tr>
                    <tr>
                        <th width="40%" style="background-color: #e8e8e8; border: 1px solid #000; padding: 4px; font-size: 9px; text-align: center;">DESCRIPCIÓN DE SERVICIOS</th>
                        <th width="20%" style="background-color: #e8e8e8; border: 1px solid #000; padding: 4px; font-size: 9px; text-align: center;">PLAZO DE ESPERA (P/E)</th>
                        <th width="40%" style="background-color: #e8e8e8; border: 1px solid #000; padding: 4px; font-size: 9px; text-align: center;">DESCRIPCIÓN</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($section['plan_services'] as $service): ?>
                        <tr>
                            <td style="border: 1px solid #000; padding: 4px; font-size: 9px;"><?= Html::encode($service['servicio'] ?? '') ?></td>
                            <td style="border: 1px solid #000; padding: 4px; font-size: 9px; text-align: center;<?= ($service['espera_status'] ?? '') === 'sin_espera' ? ' color: #107c10; font-weight: bold;' : '' ?>">
                                <?php
                                // Prefer the pre-computed display label from the controller.
                                // Falls back to the raw value if the controller wasn't updated.
                                $esperaDisplay = $service['espera_display'] ?? $service['espera'] ?? 'NO APLICA';
                                echo Html::encode($esperaDisplay);
                                ?>
                            </td>
                            <td style="border: 1px solid #000; padding: 4px; font-size: 9px;"><?= Html::encode($service['descripcion'] ?? 'Servicio incluido en el plan') ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>

        <?php if (!empty($section['exclusiones'])): ?>
            <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
                <thead>
                    <tr>
                        <th style="background-color: #2c5f8a; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px; font-size: 10px;">
                            DE LAS EXCLUSIONES
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="border: 1px solid #000; padding: 6px; font-size: 9px; line-height: 1.5;">
                            <?= nl2br(Html::encode($section['exclusiones'])) ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        <?php endif; ?>

        <div style="margin-top: 15px;"></div>
    <?php endforeach; ?>

    <!-- ============================================ -->
    <!-- GRAND TOTAL row                              -->
    <!-- ============================================ -->
    <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
        <tbody>
            <tr>
                <td colspan="3" style="background-color: #1a3a6b; border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; color: #ffffff; font-size: 11px;">
                    TOTAL MENSUAL DEL CONTRATO (<?= (int)($data['total_plan_count'] ?? 0) ?> PLANES, <?= (int)($data['total_empleados'] ?? 0) ?> AFILIADOS):
                </td>
                <td style="background-color: #1a3a6b; border: 1px solid #000; padding: 8px; text-align: right; font-weight: bold; color: #ffffff; font-size: 11px;">
                    $<?= number_format((float)($data['grand_subtotal'] ?? 0), 2, ',', '.') ?>
                </td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top: 25px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 5: DECLARACIONES Y AUTORIZACIONES    -->
    <!-- ============================================ -->
    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;">
        <thead>
            <tr>
                <th colspan="6" style="background-color: #1a3a6b; color: #ffffff; font-weight: bold; text-align: center; border: 1px solid #000; padding: 4px;">
                    DECLARACIONES Y AUTORIZACIONES
                </th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 10px; font-size: 9px; line-height: 1.6;">
                    <p>Yo, <span style="font-weight: bold; text-decoration: underline;"><?= Html::encode($data['corporate_legal_representative_name'] ?? 'N/A') ?></span>, titular de la cédula de identidad N° <span style="font-weight: bold; text-decoration: underline;"><?= Html::encode($data['corporate_legal_representative_ci'] ?? 'N/A') ?></span>, actuando en mi carácter de REPRESENTANTE LEGAL de <span style="font-weight: bold; text-decoration: underline;"><?= Html::encode($data['corporate_name'] ?? 'N/A') ?></span>, R.I.F. <span style="font-weight: bold; text-decoration: underline;"><?= Html::encode($data['corporate_rif'] ?? 'N/A') ?></span>, en lo sucesivo EL CONTRATANTE,</p>
                    <p>* Declaro que he leído cuidadosamente y totalmente, una a una, todas las cláusulas y condiciones de esta solicitud de afiliación colectiva y que la información aquí suministrada es verdadera, amplia, completa y exacta.</p>
                    <p>* Declaro que el correo electrónico suministrado me pertenece e identifica plenamente, por lo que autorizo expresamente a Sistema Integral de Salud Programado Medicina Prepagada, S.A. para enviarme todos los documentos que forman parte del contrato y cualquier comunicación pertinente por este medio.</p>
                    <p>* Doy fe de que el dinero utilizado para el pago de las cuotas proviene de una fuente lícita y su origen no guarda relación alguna con capitales, bienes, haberes, valores, títulos u operaciones producto de actividades ilícitas o que provengan de los delitos de Delincuencia Organizada u otras conductas tipificadas en la legislación venezolana.</p>
                    <p>* Autorizo a Sistema Integral de Salud Programado Medicina Prepagada, S.A. a debitar de la Cuenta Bancaria o cargar en la Tarjeta de Crédito, cuyos datos proporciono en esta solicitud, los cobros de cuotas de este servicio de medicina prepagada durante su vigencia a partir de su emisión. Y me comprometo a mantener el monto suficiente para cumplir con la obligación del pago de la cuota correspondiente.</p>
                </td>
            </tr>

            <!-- ============================================ -->
            <!-- SIGNATURE BLOCKS — CONTRATANTE + SISPSA      -->
            <!-- ============================================ -->
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 0;">

                    <table style="width: 100%; border-collapse: collapse; page-break-inside: avoid; table-layout: fixed;">
                        <tr>

                            <!-- ─── BLOCK 1: POR EL CONTRATANTE ─── -->
                            <td style="width: 50%; vertical-align: top; padding: 22px 20px 20px 20px; border-right: 1px solid #000;">

                                <!-- Blank space for the pen signature -->
                                <div style="height: 70px;"></div>

                                <!-- Signature line -->
                                <div style="border-top: 1px solid #333; width: 92%; margin: 0 auto;"></div>

                                <!-- Identity block — labels only, no underscores -->
                                <div style="min-height: 68px; margin-top: 8px; font-size: 9px; text-align: center; line-height: 1.7;">
                                    <div>
                                        <strong>NOMBRE:</strong>
                                        <?= Html::encode($data['corporate_legal_representative_name'] ?? '') ?>
                                    </div>
                                    <div>
                                        <strong>C.I.:</strong>
                                        <?= Html::encode($data['corporate_legal_representative_ci'] ?? '') ?>
                                    </div>
                                    <div><strong>CARGO:</strong> Representante Legal</div>
                                </div>

                                <!-- Empty fingerprint box -->
                                <table style="width: 100%; margin-top: 14px; border-collapse: collapse;">
                                    <tr>
                                        <td style="width: 50%;"></td>
                                        <td style="width: 50%; text-align: center;">
                                            <div style="border: 1px solid #333; width: 80px; height: 105px; margin: 0 auto; border-radius: 4px; background-color: #fafafa;"></div>
                                            <div style="font-size: 7px; color: #666; margin-top: 3px; font-weight: bold; letter-spacing: 0.5px;">
                                                HUELLA DACTILAR
                                            </div>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Signatory label -->
                                <div style="text-align: center; font-size: 9px; font-weight: bold; color: #1a3a6b; margin-top: 12px; letter-spacing: 0.6px;">
                                    POR EL CONTRATANTE
                                </div>
                            </td>

                            <!-- ─── BLOCK 2: POR SISPSA ─── -->
                            <td style="width: 50%; vertical-align: top; padding: 22px 20px 20px 20px;">

                                <!-- Blank space for the pen signature -->
                                <div style="height: 70px;"></div>

                                <!-- Signature line -->
                                <div style="border-top: 1px solid #333; width: 92%; margin: 0 auto;"></div>

                                <!-- Identity block — labels only, no underscores -->
                                <div style="min-height: 68px; margin-top: 8px; font-size: 9px; text-align: center; line-height: 1.7;">
                                    <div><strong>NOMBRE:</strong></div>
                                    <div><strong>C.I.:</strong></div>
                                    <div><strong>CARGO:</strong> Ejecutivo Autorizado</div>
                                </div>

                                <!-- Empty fingerprint box -->
                                <table style="width: 100%; margin-top: 14px; border-collapse: collapse;">
                                    <tr>
                                        <td style="width: 50%;"></td>
                                        <td style="width: 50%; text-align: center;">
                                            <div style="border: 1px solid #333; width: 80px; height: 105px; margin: 0 auto; border-radius: 4px; background-color: #fafafa;"></div>
                                            <div style="font-size: 7px; color: #666; margin-top: 3px; font-weight: bold; letter-spacing: 0.5px;">
                                                HUELLA DACTILAR
                                            </div>
                                        </td>
                                    </tr>
                                </table>

                                <!-- Signatory label -->
                                <div style="text-align: center; font-size: 9px; font-weight: bold; color: #1a3a6b; margin-top: 12px; letter-spacing: 0.6px;">
                                    POR SISPSA
                                </div>
                            </td>

                        </tr>
                    </table>

                </td>
            </tr>

            <!-- ============================================ -->
            <!-- INTERMEDIARIO DE LA ACTIVIDAD (unchanged)    -->
            <!-- ============================================ -->
            <tr>
                <td colspan="6" style="background-color: #1a3a6b; padding: 8px; border: 1px solid #000; text-align: center;">
                    <h4 style="margin: 0; font-weight: bold; color: white;">INTERMEDIARIO DE LA ACTIVIDAD ASEGURADORA</h4>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="border: 1px solid #000; padding: 8px; font-weight: bold; background-color: #e8e8e8;">NOMBRE Y APELLIDO:</td>
                <td colspan="2" style="border: 1px solid #000; padding: 8px; font-weight: bold; background-color: #e8e8e8;">CÓDIGO №:</td>
                <td colspan="2" style="border: 1px solid #000; padding: 8px; font-weight: bold; background-color: #e8e8e8;">C.I. / R.I.F. / PASAPORTE:</td>
            </tr>
            <tr>
                <td colspan="2" style="border: 1px solid #000; padding: 8px;"><?= Html::encode($data['intermediary_name'] ?? '') ?></td>
                <td colspan="2" style="border: 1px solid #000; padding: 8px;"><?= Html::encode($data['intermediary_code'] ?? '') ?></td>
                <td colspan="2" style="border: 1px solid #000; padding: 8px;"><?= Html::encode($data['intermediary_ci'] ?? '') ?></td>
            </tr>
            <tr>
                <td colspan="6" style="border: 1px solid #000; padding: 8px; text-align: center; background-color: #f0f4f8;">
                    <p style="font-size: 8px; margin: 0;">Aprobado por la Superintendencia de la Actividad Aseguradora según Providencia Nº SAA-SUT-34169 de fecha 13/02/2025</p>
                </td>
            </tr>
        </tbody>
    </table>

</div>