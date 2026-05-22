<?php

use yii\helpers\Html;
?>
<div class="pdf-container">

    <!-- ============================================ -->
    <!-- PERFECTLY BALANCED HEADER                    -->
    <!-- Logo: Left | Company Info: Center | Type: Right -->
    <!-- ============================================ -->
    <table style="width: 100%; margin-bottom: 20px;">
        <tr>
            <!-- LEFT: LOGO -->
            <td style="width: 25%; vertical-align: middle;">
                <div style="text-align: left;">
                    <img src="<?= Html::encode($logo) ?>" alt="SISPSA" style="width: 130px; height: auto; border-radius: 12px; box-shadow: 0 3px 10px rgba(0,0,0,0.15);">
                </div>
            </td>

            <!-- CENTER: COMPANY INFORMATION -->
            <td style="width: 50%; vertical-align: middle; text-align: center;">
                <div style="font-size: 20px; font-weight: bold; color: #1a3a6b; letter-spacing: 1.5px;">SISTEMA INTEGRAL DE SALUD PROGRAMADO</div>
                <div style="font-size: 15px; font-weight: 600; color: #2c5f8a; margin-top: 5px;">Medicina Prepagada, S.A.</div>
                <div style="font-size: 9px; color: #555; margin-top: 12px; line-height: 1.5;">
                    Inscrita en la Superintendencia de la Actividad Aseguradora bajo el No. MP000013<br>
                    R.I.F.: J-50654922
                </div>
            </td>

            <!-- RIGHT: AFFILIATION TYPE (ORIGINAL LAYOUT RESTORED) -->
            <td style="width: 25%; vertical-align: middle; text-align: right;">
                <div style="background-color: #f0f4f8; border: 1px solid #d0d7de; border-radius: 10px; padding: 10px 15px; display: inline-block; text-align: left;">
                    <div style="font-size: 8px; font-weight: bold; color: #666; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">TIPO DE AFILIACIÓN</div>
                    <div style="display: flex; gap: 20px;">
                        <span class="checkbox-group">
                            <span class="checkbox"><?= ($data['affiliation_type_id'] ?? 1) == 1 ? '☒' : '☐' ?></span>
                            INDIVIDUAL
                        </span>
                        <span class="checkbox-group">
                            <span class="checkbox"><?= ($data['affiliation_type_id'] ?? 1) == 2 ? '☒' : '☐' ?></span>
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
    <table class="family-table">
        <thead>
            <tr>
                <th colspan="8">DATOS DEL CONTRATO</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="2" style="font-size: 10px;">
                    <strong>CONTRATO N°:</strong> <?= Html::encode($data['contract_number'] ?? 'PENDIENTE') ?>
                    <?php if (($data['has_active_contract'] ?? false) === false): ?>
                        <br><small style="color: #ff0000;">(Contrato en proceso de activación)</small>
                    <?php endif; ?>
                </td>
                <td colspan="2" style="font-size: 10px;">
                    <strong>RECIBO №:</strong> <?= Html::encode($data['receipt_number'] ?? 'PENDIENTE') ?>
                    <?php if (empty($data['receipt_number'])): ?>
                        <br><small style="color: #ff9900;">(Se generará al primer pago)</small>
                    <?php endif; ?>
                </td>
                <td colspan="4" style="font-size: 10px;">
                    <strong>TOTAL CUOTAS DE AFILIACIÓN:</strong> <?= $data['total_cuotas'] ?? 12 ?> cuotas
                    <?php if (!empty($data['total_monto_cuotas'])): ?>
                        <br><small>Total: <?= Yii::$app->formatter->asCurrency($data['total_monto_cuotas'], 'USD') ?></small>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td colspan="2" style="font-size: 10px; background-color: #f9f9f9;">
                    <strong>FECHA INICIO:</strong>
                    <?php
                    $startDate = $data['contract_start_date'] ?? null;
                    echo $startDate ? Yii::$app->formatter->asDate($startDate, 'dd/MM/yyyy') : 'N/A';
                    ?>
                </td>
                <td colspan="2" style="font-size: 10px; background-color: #f9f9f9;">
                    <strong>FECHA FIN:</strong>
                    <?php
                    $endDate = $data['contract_end_date'] ?? null;
                    echo $endDate ? Yii::$app->formatter->asDate($endDate, 'dd/MM/yyyy') : 'N/A';
                    ?>
                </td>
                <td colspan="2" style="font-size: 10px; background-color: #f9f9f9;">
                    <strong>MONTO MENSUAL:</strong>
                    <?= Yii::$app->formatter->asCurrency($data['monthly_amount'] ?? 0, 'USD') ?>
                </td>
                <td colspan="2" style="font-size: 10px; background-color: #f9f9f9;">
                    <strong>CLÍNICA:</strong>
                    <?= Html::encode($data['clinica_name'] ?? 'No asignada') ?>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- SPACE BETWEEN SECTION 1 AND SECTION 2 -->
    <div style="margin-top: 25px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 2: DATOS DEL PROPUESTO AFILIADO TITULAR -->
    <!-- ============================================ -->
    <table class="family-table">
        <thead>
            <tr>
                <th colspan="8">DATOS DEL PROPUESTO AFILIADO TITULAR</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="2" style="font-size: 10px;">Nombre y Apellido: <?= $data['proposed_affiliate_name'] ?? 'N/A' ?><br></td>
                <td colspan="2" style="font-size: 10px;">C.I. / R.I.F./ Pasaporte: <?= $data['proposed_affiliate_ci'] ?? 'N/A' ?></td>
                <td colspan="2" style="font-size: 10px;">Nacionalidad: <?= $data['proposed_affiliate_nationality'] ?? 'N/A' ?></td>
                <td colspan="2" style="font-size: 10px;">Estado civil: <?= $data['proposed_affiliate_marital_status'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td colspan="1" style="font-size: 10px;">Lugar Nacimiento: <?= $data['proposed_affiliate_birthplace'] ?? 'N/A' ?></td>
                <td colspan="1" style="font-size: 10px;">Fecha Nacimiento: <?= isset($data['proposed_affiliate_birthdate']) && $data['proposed_affiliate_birthdate'] ? date('d/m/Y', strtotime($data['proposed_affiliate_birthdate'])) : 'N/A' ?></td>
                <td colspan="2" style="font-size: 10px;">Sexo: <?= $data['proposed_affiliate_sex'] ?? 'N/A' ?></td>
                <td colspan="2" style="font-size: 10px;">Profesión: <?= $data['proposed_affiliate_profession'] ?? 'N/A' ?></td>
                <td colspan="2" style="font-size: 10px;">Ocupación: <?= $data['proposed_affiliate_occupation'] ?? 'N/A' ?></td>
            </tr>

            <!-- ROW: Actividad Económica aligned properly -->
            <tr>
                <td colspan="4" style="font-size: 10px; vertical-align: top;">
                    <strong>Actividad Económica:</strong><br>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_economic_activity'] ?? '') == 'Industrial' ? '☑' : '☐' ?></span> Industrial</span>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_economic_activity'] ?? '') == 'Comercial' ? '☑' : '☐' ?></span> Comercial</span>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_economic_activity'] ?? '') == 'Profesional' ? '☑' : '☐' ?></span> Profesional</span>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_economic_activity'] ?? '') == 'Gubernamental' ? '☑' : '☐' ?></span> Gubernamental</span>
                </td>
                <td colspan="2" style="font-size: 10px; vertical-align: top;">
                    <strong>Si es Comerciante indicar Ramo:</strong><br>
                    <?= $data['proposed_affiliate_commercial_branch'] ?? 'N/A' ?>
                </td>
                <td colspan="2" style="font-size: 10px; vertical-align: top;">
                    <strong>Descripción de la Actividad:</strong><br>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_activity_description'] ?? '') == 'Independiente' ? '☑' : '☐' ?></span> Independiente</span>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_activity_description'] ?? '') == 'Dependiente' ? '☑' : '☐' ?></span> Dependiente</span>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_activity_description'] ?? '') == 'Societaria' ? '☑' : '☐' ?></span> Societaria</span>
                </td>
            </tr>

            <tr>
                <td colspan="8" style="font-size: 10px;">
                    <strong>Ingreso Anual Bs:</strong><br>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_annual_income'] ?? '') == 'De 1 a 5 Salarios mínimos' ? '☑' : '☐' ?></span> De 1 a 5</span>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_annual_income'] ?? '') == 'De 6 a 10 Salarios mínimos' ? '☑' : '☐' ?></span> De 6 a 10</span>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_annual_income'] ?? '') == 'De 11 a 20 Salarios mínimos' ? '☑' : '☐' ?></span> De 11 a 20</span>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_annual_income'] ?? '') == 'De 20 Salarios mínimos en adelante' ? '☑' : '☐' ?></span> 20+</span>
                </td>
            </tr>
            <tr>
                <td colspan="6" style="font-size: 10px;">Dirección de Residencia: <?= $data['proposed_affiliate_residence_address'] ?? 'N/A' ?></td>
                <td colspan="2" style="font-size: 10px;">Teléfono Residencia: <?= $data['proposed_affiliate_phone_residence'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td colspan="6" style="font-size: 10px;">Dirección de Oficina: <?= $data['proposed_affiliate_office_address'] ?? 'N/A' ?></td>
                <td colspan="2" style="font-size: 10px;">Teléfono Oficina: <?= $data['proposed_affiliate_phone_office'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td colspan="8" style="font-size: 10px;">Dirección de Cobro: <?= $data['proposed_affiliate_billing_address'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td colspan="3" style="font-size: 10px;">Teléfono Celular: <?= $data['proposed_affiliate_cell_phone'] ?? 'N/A' ?></td>
                <td colspan="5" style="font-size: 10px;">Correo Electrónico: <?= $data['proposed_affiliate_email'] ?? 'N/A' ?></td>
            </tr>
        </tbody>
    </table>

    <!-- SPACE BETWEEN SECTION 2 AND SECTION 3 -->
    <div style="margin-top: 25px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 3: DATOS DEL CONTRATANTE             -->
    <!-- ============================================ -->
    <table class="family-table">
        <thead>
            <tr>
                <th colspan="8">DATOS DEL CONTRATANTE (De ser diferente al PROPUESTO AFILIADO TITULAR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="2" style="font-size: 10px;">Nombre y Apellido: <?= $data['contracting_party_name'] ?? 'N/A' ?><br></td>
                <td colspan="2" style="font-size: 10px;">C.I. / R.I.F./ Pasaporte: <?= $data['contracting_party_ci'] ?? 'N/A' ?></td>
                <td colspan="2" style="font-size: 10px;">Nacionalidad: <?= $data['contracting_party_nationality'] ?? 'N/A' ?></td>
                <td colspan="2" style="font-size: 10px;">Estado civil: <?= $data['contracting_party_marital_status'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td colspan="1" style="font-size: 10px;">Lugar Nacimiento: <?= $data['contracting_party_birthplace'] ?? 'N/A' ?></td>
                <td colspan="1" style="font-size: 10px;">Fecha Nacimiento: <?= isset($data['contracting_party_birthdate']) && $data['contracting_party_birthdate'] ? date('d/m/Y', strtotime($data['contracting_party_birthdate'])) : 'N/A' ?></td>
                <td colspan="2" style="font-size: 10px;">Sexo: <?= $data['contracting_party_sex'] ?? 'N/A' ?></td>
                <td colspan="2" style="font-size: 10px;">Profesión: <?= $data['contracting_party_profession'] ?? 'N/A' ?></td>
                <td colspan="2" style="font-size: 10px;">Ocupación: <?= $data['contracting_party_occupation'] ?? 'N/A' ?></td>
            </tr>

            <!-- ROW: Actividad Económica aligned properly for CONTRATANTE -->
            <tr>
                <td colspan="4" style="font-size: 10px; vertical-align: top;">
                    <strong>Actividad Económica:</strong><br>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_economic_activity'] ?? '') == 'Industrial' ? '☑' : '☐' ?></span> Industrial</span>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_economic_activity'] ?? '') == 'Comercial' ? '☑' : '☐' ?></span> Comercial</span>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_economic_activity'] ?? '') == 'Profesional' ? '☑' : '☐' ?></span> Profesional</span>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_economic_activity'] ?? '') == 'Gubernamental' ? '☑' : '☐' ?></span> Gubernamental</span>
                </td>
                <td colspan="2" style="font-size: 10px; vertical-align: top;">
                    <strong>Si es Comerciante indicar Ramo:</strong><br>
                    <?= $data['proposed_affiliate_commercial_branch'] ?? 'N/A' ?>
                </td>
                <td colspan="2" style="font-size: 10px; vertical-align: top;">
                    <strong>Descripción de la Actividad:</strong><br>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_activity_description'] ?? '') == 'Independiente' ? '☑' : '☐' ?></span> Independiente</span>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_activity_description'] ?? '') == 'Dependiente' ? '☑' : '☐' ?></span> Dependiente</span>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_activity_description'] ?? '') == 'Societaria' ? '☑' : '☐' ?></span> Societaria</span>
                </td>
            </tr>

            <tr>
                <td colspan="8" style="font-size: 10px;">
                    <strong>Ingreso Anual Bs:</strong><br>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_annual_income'] ?? '') == 'De 1 a 5 Salarios mínimos' ? '☑' : '☐' ?></span> De 1 a 5</span>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_annual_income'] ?? '') == 'De 6 a 10 Salarios mínimos' ? '☑' : '☐' ?></span> De 6 a 10</span>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_annual_income'] ?? '') == 'De 11 a 20 Salarios mínimos' ? '☑' : '☐' ?></span> De 11 a 20</span>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_annual_income'] ?? '') == 'De 20 Salarios mínimos en adelante' ? '☑' : '☐' ?></span> 20+</span>
                </td>
            </tr>
            <tr>
                <td colspan="6" style="font-size: 10px;">Dirección de Residencia: <?= $data['contracting_party_residence_address'] ?? 'N/A' ?></td>
                <td colspan="2" style="font-size: 10px;">Teléfono Residencia: <?= $data['contracting_party_phone_residence'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td colspan="6" style="font-size: 10px;">Dirección de Oficina: <?= $data['contracting_party_office_address'] ?? 'N/A' ?></td>
                <td colspan="2" style="font-size: 10px;">Teléfono Oficina: <?= $data['contracting_party_phone_office'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td colspan="8" style="font-size: 10px;">Dirección de Cobro: <?= $data['contracting_party_billing_address'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td colspan="3" style="font-size: 10px;">Teléfono Celular: <?= $data['contracting_party_cell_phone'] ?? 'N/A' ?></td>
                <td colspan="5" style="font-size: 10px;">Correo Electrónico: <?= $data['contracting_party_email'] ?? 'N/A' ?></td>
            </tr>
        </tbody>
    </table>

    <?php if ($data['has_corporate_relation'] ?? false): ?>
        <!-- SPACE BETWEEN SECTIONS -->
        <div style="margin-top: 25px;"></div>

        <!-- ============================================ -->
        <!-- SECTION 4: DATOS CORPORATIVOS                -->
        <!-- ============================================ -->
        <table class="family-table">
            <thead>
                <tr>
                    <th colspan="8">DATOS CORPORATIVOS (Persona Jurídica)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2" style="font-size: 10px;">Razón Social: <?= $data['corporate_name'] ?? 'N/A' ?></td>
                    <td colspan="1" style="font-size: 10px;">R.I.F.: <?= $data['corporate_rif'] ?? 'N/A' ?></td>
                    <td colspan="2" style="font-size: 10px;">Nº de Registro Mercantil: <?= $data['corporate_mercantile_register'] ?? 'N/A' ?></td>
                    <td colspan="2" style="font-size: 10px;">Nº de Tomo: <?= $data['corporate_tome'] ?? 'N/A' ?></td>
                    <td colspan="1" style="font-size: 10px;">F/Registro: <?= isset($data['corporate_registration_date']) && $data['corporate_registration_date'] ? date('d/m/Y', strtotime($data['corporate_registration_date'])) : 'N/A' ?></td>
                </tr>
                <tr>
                    <td colspan="8" style="font-size: 10px;">Actividad Económica: <?= $data['corporate_economic_activity'] ?? 'N/A' ?></td>
                </tr>
                <tr>
                    <td colspan="6" style="font-size: 10px;">Dirección: <?= $data['corporate_address'] ?? 'N/A' ?></td>
                    <td colspan="2" style="font-size: 10px;">Teléfono: <?= $data['corporate_phone'] ?? 'N/A' ?></td>
                </tr>
                <tr>
                    <td colspan="8" style="font-size: 10px;">Productos y Servicios que ofrece: <?= $data['corporate_products_services'] ?? 'N/A' ?></td>
                </tr>
                <tr>
                    <td colspan="4" style="font-size: 10px;">Utilidad del ejercicio económico inmediatamente anterior, cuando aplique: <?= $data['corporate_profit'] ?? 'N/A' ?></td>
                    <td colspan="4" style="font-size: 10px;">Patrimonio, según último estado de resultados o estado de ganancias y pérdidas: <?= $data['corporate_equity'] ?? 'N/A' ?></td>
                </tr>
            </tbody>
        </table>

        <!-- SPACE BETWEEN SECTIONS -->
        <div style="margin-top: 25px;"></div>

        <!-- ============================================ -->
        <!-- SECTION 5: DATOS DEL REPRESENTANTE LEGAL     -->
        <!-- ============================================ -->
        <table class="family-table">
            <thead>
                <tr>
                    <th colspan="8">DATOS DEL REPRESENTANTE LEGAL</th>
                <tr>
            </thead>
            <tbody>
                <tr>
                    <td colspan="2" style="font-size: 10px;">Nombre y Apellido: <?= $data['legal_representative_name'] ?? 'N/A' ?></td>
                    <td colspan="2" style="font-size: 10px;">C.I. / R.I.F./ Pasaporte: <?= $data['legal_representative_ci'] ?? 'N/A' ?></td>
                    <td colspan="2" style="font-size: 10px;">Nacionalidad: <?= $data['legal_representative_nationality'] ?? 'N/A' ?></td>
                    <td colspan="2" style="font-size: 10px;">Estado civil: <?= $data['legal_representative_marital_status'] ?? 'N/A' ?></td>
                </tr>
                <tr>
                    <td colspan="1" style="font-size: 10px;">Lugar Nacimiento: <?= $data['legal_representative_birthplace'] ?? 'N/A' ?></td>
                    <td colspan="1" style="font-size: 10px;">Fecha Nacimiento: <?= isset($data['legal_representative_birthdate']) && $data['legal_representative_birthdate'] ? date('d/m/Y', strtotime($data['legal_representative_birthdate'])) : 'N/A' ?></td>
                    <td colspan="2" style="font-size: 10px;">Sexo: <?= $data['legal_representative_sex'] ?? 'N/A' ?></td>
                    <td colspan="2" style="font-size: 10px;">Profesión: <?= $data['legal_representative_profession'] ?? 'N/A' ?></td>
                    <td colspan="2" style="font-size: 10px;">Ocupación: <?= $data['legal_representative_occupation'] ?? 'N/A' ?></td>
                </tr>
                <tr>
                    <td colspan="4" style="font-size: 10px;">Descripción de la Actividad: <?= $data['legal_representative_activity_description'] ?? 'N/A' ?></td>
                    <td colspan="2" style="font-size: 10px;">Dirección: <?= $data['legal_representative_address'] ?? 'N/A' ?></td>
                    <td colspan="2" style="font-size: 10px;">Teléfono: <?= $data['legal_representative_phone'] ?? 'N/A' ?></td>
                </tr>
            </tbody>
        </table>
    <?php endif; ?>

    <!-- SPACE BETWEEN SECTIONS -->
    <div style="margin-top: 25px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 6: PLAN SOLICITADO Y COBERTURAS      -->
    <!-- ============================================ -->
    <table class="family-table">
        <thead>
            <tr>
                <th colspan="8">PLAN SOLICITADO Y COBERTURAS</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th colspan="2" style="text-align: center;">PLAN SOLICITADO</th>
                <th colspan="2" style="text-align: center;">Moneda</th>
                <th colspan="2" style="text-align: center;">Deducible</th>
                <th colspan="2" style="text-align: center;">Límite de Cobertura</th>
            </tr>
            <tr>
                <td colspan="2" style="text-align: center;"><?= $data['plan_selected'] ?? 'N/A' ?></td>
                <td colspan="2" style="text-align: center;"><?= $data['plan_currency'] ?? 'N/A' ?></td>
                <td colspan="2" style="text-align: center;"><?= $data['plan_deductible'] ?? 'N/A' ?></td>
                <td colspan="2" style="text-align: center;"><?= $data['plan_coverage_limit'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <th colspan="4" style="text-align: center;">Cobertura Opcional (El Contratante no está obligado a contratar esta cobertura)</th>
                <th colspan="2" style="text-align: center;">Deducible</th>
                <th colspan="2" style="text-align: center;">Límite de Cobertura</th>
            </tr>
            <tr>
                <td colspan="4" style="text-align: center;">
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['maternity_coverage'] ?? false) ? '☑' : '☐' ?></span> Servicio de Maternidad</span>
                </td>
                <td colspan="2" style="text-align: center;"><?= $data['maternity_deductible'] ?? 'N/A' ?></td>
                <td colspan="2" style="text-align: center;"><?= $data['maternity_coverage_limit'] ?? 'N/A' ?></td>
            </tr>
        </tbody>
    </table>

    <!-- SPACE BETWEEN SECTIONS -->
    <div style="margin-top: 25px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 7: GRUPO FAMILIAR                    -->
    <!-- ============================================ -->
    <table class="family-table">
        <thead>
            <tr>
                <th colspan="5">GRUPO FAMILIAR: (PERSONAS A ASEGURAR, ADEMÁS DEL AFILIADO TITULAR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th width="30%">Apellidos y Nombres</th>
                <th width="20%">Cédula de Identidad</th>
                <th width="20%">Parentesco</th>
                <th width="10%">Sexo</th>
                <th width="20%">F/Nacimiento</th>
            </tr>
            <?php if (!empty($data['family_group'])): ?>
                <?php foreach ($data['family_group'] as $member): ?>
                    <tr>
                        <td class="underline" style="text-align: center;"><?= htmlspecialchars($member['name'] ?? '') ?></td>
                        <td class="underline" style="text-align: center;"><?= htmlspecialchars($member['ci'] ?? '') ?></td>
                        <td class="underline" style="text-align: center;"><?= htmlspecialchars($member['relationship'] ?? '') ?></td>
                        <td class="underline" style="text-align: center;"><?= htmlspecialchars($member['sex'] ?? '') ?></td>
                        <td class="underline" style="text-align: center;"><?= isset($member['birthdate']) && $member['birthdate'] ? date('d/m/Y', strtotime($member['birthdate'])) : '' ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <?php for ($i = count($data['family_group'] ?? []); $i < 5; $i++): ?>
                <tr>
                    <td class="underline">&nbsp;</td>
                    <td class="underline">&nbsp;</td>
                    <td class="underline">&nbsp;</td>
                    <td class="underline">&nbsp;</td>
                    <td class="underline">&nbsp;</td>
                </tr>
            <?php endfor; ?>
        </tbody>
    </table>

    <!-- SPACE BETWEEN SECTIONS -->
    <div style="margin-top: 25px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 8: BENEFICIARIO                      -->
    <!-- ============================================ -->
    <table class="family-table">
        <thead>
            <tr>
                <th colspan="5">BENEFICIARIO EN CASO DE MUERTE DEL PROPUESTO AFILIADO TITULAR</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <th width="30%">Apellidos y Nombres</th>
                <th width="20%">Cédula de Identidad</th>
                <th width="20%">Parentesco</th>
                <th width="10%">Sexo</th>
                <th width="20%">F/Nacimiento</th>
            </tr>
            <tr>
                <td class="underline" style="text-align: center;"><?= htmlspecialchars($data['beneficiary_name'] ?? 'N/A') ?></td>
                <td class="underline" style="text-align: center;"><?= htmlspecialchars($data['beneficiary_ci'] ?? 'N/A') ?></td>
                <td class="underline" style="text-align: center;"><?= htmlspecialchars($data['beneficiary_relationship'] ?? 'N/A') ?></td>
                <td class="underline" style="text-align: center;"><?= htmlspecialchars($data['beneficiary_sex'] ?? 'N/A') ?></td>
                <td class="underline" style="text-align: center;"><?= isset($data['beneficiary_birthdate']) && $data['beneficiary_birthdate'] ? date('d/m/Y', strtotime($data['beneficiary_birthdate'])) : 'N/A' ?></td>
            </tr>
        </tbody>
    </table>

    <p class="note">En caso de muerte de algún otro AFILIADO en este seguro el BENEFICIARIO es el propuesto AFILIADO TITULAR</p>

    <!-- SPACE BETWEEN SECTIONS -->
    <div style="margin-top: 25px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 9: DATOS BANCARIOS                   -->
    <!-- ============================================ -->
    <table class="family-table">
        <thead>
            <tr>
                <th colspan="8">DATOS BANCARIOS DEL CONTRATANTE</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td width="25%">Nombre del Titular:</td>
                <td width="25%">C.I.:</td>
                <td width="25%">Nro. Cuenta/Tarjeta:</td>
                <td width="25%">Banco:</td>
            </tr>
            <tr>
                <td class="underline"><?= $data['bank_account_holder_name'] ?? '' ?></td>
                <td class="underline"><?= $data['bank_account_ci'] ?? '' ?></td>
                <td class="underline"><?= $data['bank_account_number'] ?? '' ?></td>
                <td class="underline"><?= $data['bank_name'] ?? '' ?></td>
            </tr>
            <tr>
                <td colspan="4">
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['bank_account_type'] ?? '') == 'Cuenta Corriente' ? '☑' : '☐' ?></span> Cuenta Corriente</span>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['bank_account_type'] ?? '') == 'Cuenta Ahorro' ? '☑' : '☐' ?></span> Cuenta Ahorro</span>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['bank_account_type'] ?? '') == 'Tarjeta Crédito Visa' ? '☑' : '☐' ?></span> Tarjeta Crédito Visa</span>
                    <span class="checkbox-group"><span class="checkbox"><?= ($data['bank_account_type'] ?? '') == 'Tarjeta Crédito MasterCard' ? '☑' : '☐' ?></span> Tarjeta Crédito MasterCard</span>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- SPACE BETWEEN SECTIONS -->
    <div style="margin-top: 25px;"></div>

    <!-- ============================================ -->
    <!-- SECTION 10: DECLARACIONES Y AUTORIZACIONES   -->
    <!-- ============================================ -->
    <table class="family-table">
        <thead>
            <tr>
                <th colspan="6">DECLARACIONES Y AUTORIZACIONES</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td colspan="6" style="padding: 0;">
                    <p>Yo, <span class="underline"><?= $data['declaration_proposed_affiliate_name'] ?? 'N/A' ?></span>, titular de la cédula de identidad N° <span class="underline"><?= $data['declaration_proposed_affiliate_ci'] ?? 'N/A' ?></span> en mi carácter de PROPUESTO AFILIADO TITULAR,</p>
                    <p>* Declaro que he leído cuidadosamente y totalmente, una a una, todas las preguntas y respuestas consignadas en esta solicitud de seguro y que ellas son verdaderas, amplias, completas y exactas.</p>
                    <p>* Declaro que el correo electrónico suministrado me pertenece e identifica plenamente, por lo que autorizo expresamente a Sistema Integral de Salud Programado Medicina Prepagada, S.A. para enviarme todos los documentos que forman parte del contrato y cualquier comunicación pertinente por este medio.</p>

                    <?php if (!empty(trim($data['declaration_contracting_party_name'] ?? ''))): ?>
                        <p>Yo, <span class="underline"><?= $data['declaration_contracting_party_name'] ?? 'N/A' ?></span>, titular de la cédula de identidad N° <span class="underline"><?= $data['declaration_contracting_party_ci'] ?? 'N/A' ?></span> en mi carácter de CONTRATANTE,</p>
                        <p>* Doy fe de que el dinero utilizado para el pago de las cuotas, provienen de una fuente lícita y su origen no guarda relación alguna con capitales, bienes, haberes, valores, títulos u operaciones, producto de actividades ilícitas o que provenga de los delitos de Delincuencia Organizada u otras conductas tipificadas en la legislación venezolana.</p>
                        <p>* Autorizo a Sistema Integral de Salud Programado Medicina Prepagada, S.A. a debitar de la Cuenta Bancaria / o cargar en la Tarjeta de Crédito, cuyos datos proporciono en esta solicitud, los cobros de cuotas de este servicio de medicina prepagada durante su vigencia a partir de su emisión. Y me comprometo a mantener el monto suficiente para cumplir con la obligación del pago de la cuota correspondiente.</p>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td colspan="6" style="padding: 0;">
                    <img src="<?= Html::encode($firmas) ?>" alt="firmas" style="width: 100%;">
                </td>
            </tr>
            <tr>
                <td colspan="6" class="intermediario-title" style="background-color: #1a3a6b; padding: 8px; border-bottom: 1px solid #ccc;">
                    <h4 style="margin: 0; font-weight: bold; color: white; text-align: center;">INTERMEDIARIO DE LA ACTIVIDAD ASEGURADORA</h4>
            </tr>
            <tr>
                <td colspan="2" style="font-weight: bold; padding: 8px;">NOMBRE Y APELLIDO:</td>
                <td colspan="2" style="font-weight: bold; padding: 8px;">CÓDIGO №:</td>
                <td colspan="2" style="font-weight: bold; padding: 8px;">C.I. / R.I.F. /Pasaporte:</td>
            </tr>
            <tr>
                <td colspan="2" style="padding: 8px; border-bottom: 1px solid #000;">
                    <?= $data['intermediary_name'] ?? '' ?>
                </td>
                <td colspan="2" style="padding: 8px; border-bottom: 1px solid #000;">
                    <?= $data['intermediary_code'] ?? '' ?>
                </td>
                <td colspan="2" style="padding: 8px; border-bottom: 1px solid #000;">
                    <?= $data['intermediary_ci'] ?? '' ?>
                </td>
            </tr>
            <tr>
                <td colspan="6" style="padding: 8px; text-align: center;">
                    <p class="approval">Aprobado por la Superintendencia de la Actividad Aseguradora según Providencia Nº SAA-SUT-34169 de fecha 13/02/2025</p>
                </td>
            </tr>
        </tbody>
    </table>

</div>