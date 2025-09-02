<?php
use yii\helpers\Html;
?>
<div class="pdf-container">


<table style="width: 100%;">
    <tr>
        <td style="vertical-align: top; width: 65%;">
            <img src="<?= Html::encode($logo) ?>" alt="Logo SISPSA" class="logo-superior-izquierda" style="width: 100px;">
            <div style="font-size: 10px;">
                <div>Inscrita en la Superintendencia de la Actividad Aseguradora bajo el No. MP000013</div>
                <div>R.I.F.: J-50654922</div>
            </div>
        </td>
        <td style="vertical-align: top; width: 40%;">
            <div class="header" style="margin-top: 30px;">
                <div class="header-right">
                    <div>Contrato de Servicios de Medicina Prepagada</div>
                    <div>Solicitud de Afiliación</div>
                </div>
            </div>
            <div class="affiliation-type">
                <span class="checkbox-group"><span class="checkbox"><?= ($data['affiliation_type'] ?? '') == 'INDIVIDUAL' ? '☑' : '☐' ?></span> INDIVIDUAL</span>
                <span class="checkbox-group"><span class="checkbox"><?= ($data['affiliation_type'] ?? '') == 'COLECTIVO' ? '☑' : '☐' ?></span> COLECTIVO</span>
            </div>
        </td>
    </tr>
</table>
<br>

    <table class="family-table">
        <thead>
            <tr>
                <th colspan="8">DATOS DEL PROPUESTO AFILIADO TITULAR</th>
            </tr>
        </thead>
        <tr>
            <td colspan="2" style="font-size: 10px;">Nombre y Apellido: <?= $data['proposed_affiliate_name'] ?? '' ?><br></td>
            <td colspan="2" style="font-size: 10px;">C.I. / R.I.F./ Pasaporte: <?= $data['proposed_affiliate_ci'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Nacionalidad: <?= $data['proposed_affiliate_nationality'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Estado civil: <?= $data['proposed_affiliate_marital_status'] ?? '' ?></td>
        </tr>
        <tr>
            <td colspan="1" style="font-size: 10px;">Lugar Nacimiento: <?= $data['proposed_affiliate_birthplace'] ?? '' ?></td>
            <td colspan="1" style="font-size: 10px;">Fecha Nacimiento: <?= isset($data['proposed_affiliate_birthdate']) && $data['proposed_affiliate_birthdate'] ? date('d/m/Y', strtotime($data['proposed_affiliate_birthdate'])) : '' ?></td>
            <td colspan="2" style="font-size: 10px;">Sexo: <?= $data['proposed_affiliate_sex'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Profesión: <?= $data['proposed_affiliate_profession'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Ocupación: <?= $data['proposed_affiliate_occupation'] ?? '' ?></td>
        </tr>
        <tr>
            <td colspan="4" style="font-size: 10px;">
            Actividad Económica:<br>
            <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_economic_activity'] ?? '') == 'Industrial' ? '☑' : '☐' ?></span> Industrial</span>
            <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_economic_activity'] ?? '') == 'Comercial' ? '☑' : '☐' ?></span> Comercial</span>
            <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_economic_activity'] ?? '') == 'Profesional' ? '☑' : '☐' ?></span> Profesional</span>
            <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_economic_activity'] ?? '') == 'Gubernamental' ? '☑' : '☐' ?></span> Gubernamental</span>
            </td>
            <td colspan="2" style="font-size: 10px;">Si es Comerciante indicar Ramo: <?= $data['proposed_affiliate_commercial_branch'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;"><br>
            Descripción de la Actividad:<br>
            <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_activity_description'] ?? '') == 'Independiente' ? '☑' : '☐' ?></span> Independiente</span>
            <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_activity_description'] ?? '') == 'Dependiente' ? '☑' : '☐' ?></span> Dependiente</span>
            <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_activity_description'] ?? '') == 'Societaria' ? '☑' : '☐' ?></span> Societaria</span>
            </td>
        </tr>
        <tr>
            <td colspan="8" style="font-size: 10px;">
                Ingreso Anual Bs:<br>
                <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_annual_income'] ?? '') == 'De 1 a 5 Salarios mínimos' ? '☑' : '☐' ?></span> De 1 a 5</span>
                <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_annual_income'] ?? '') == 'De 6 a 10 Salarios mínimos' ? '☑' : '☐' ?></span> De 6 a 10</span>
                <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_annual_income'] ?? '') == 'De 11 a 20 Salarios mínimos' ? '☑' : '☐' ?></span> De 11 a 20</span>
                <span class="checkbox-group"><span class="checkbox"><?= ($data['proposed_affiliate_annual_income'] ?? '') == 'De 20 Salarios mínimos en adelante' ? '☑' : '☐' ?></span> 20+</span>
            </td>
        </tr>
        <tr>
            <td colspan="6" style="font-size: 10px;">Dirección de Residencia: <?= $data['proposed_affiliate_residence_address'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Teléfono Residencia: <?= $data['proposed_affiliate_phone_residence'] ?? '' ?></td>
        </tr>
        <tr>
            <td colspan="6" style="font-size: 10px;">Dirección de Oficina: <?= $data['proposed_affiliate_office_address'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Teléfono Oficina: <?= $data['proposed_affiliate_phone_office'] ?? '' ?></td>
        </tr>
        <tr>
            <td colspan="8" style="font-size: 10px;">Dirección de Cobro: <?= $data['proposed_affiliate_office_address'] ?? '' ?></td>
        </tr>
        <tr>
            <td colspan="3" style="font-size: 10px;">Teléfono Celular: <?= $data['proposed_affiliate_cell_phone'] ?? '' ?></td>
            <td colspan="5" style="font-size: 10px;">Correo Electrónico: <?= $data['proposed_affiliate_email'] ?? '' ?></td>
        </tr>
    </table>

    <table class="family-table">
         <thead>
            <tr>
                <th colspan="8">DATOS DEL CONTRATANTE (De ser diferente al PROPUESTO AFILIADO TITULAR)</th>
            </tr>
        </thead>
        <tr>
            <td colspan="2" style="font-size: 10px;">Nombre y Apellido: <?= $data['contracting_party_name'] ?? '' ?><br></td>
            <td colspan="2" style="font-size: 10px;">C.I. / R.I.F./ Pasaporte: <?= $data['contracting_party_ci'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Nacionalidad: <?= $data['contracting_party_nationality'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Estado civil: <?= $data['contracting_party_marital_status'] ?? '' ?></td>
        </tr>
        <tr>
            <td colspan="1" style="font-size: 10px;">Lugar Nacimiento: <?= $data['contracting_party_birthplace'] ?? '' ?></td>
            <td colspan="1" style="font-size: 10px;">Fecha Nacimiento: <?= isset($data['contracting_party_birthdate']) && $data['contracting_party_birthdate'] ? date('d/m/Y', strtotime($data['proposed_affiliate_birthdate'])) : '' ?></td>
            <td colspan="2" style="font-size: 10px;">Sexo: <?= $data['contracting_party_sex'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Profesión: <?= $data['contracting_party_profession'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Ocupación: <?= $data['contracting_party_occupation'] ?? '' ?></td>
        </tr>
        <tr>
         <td colspan="4" style="font-size: 10px;">
            Actividad Económica:<br>
            <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_economic_activity'] ?? '') == 'Industrial' ? '☑' : '☐' ?></span> Industrial</span>
            <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_economic_activity'] ?? '') == 'Comercial' ? '☑' : '☐' ?></span> Comercial</span>
            <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_economic_activity'] ?? '') == 'Profesional' ? '☑' : '☐' ?></span> Profesional</span>
            <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_economic_activity'] ?? '') == 'Gubernamental' ? '☑' : '☐' ?></span> Gubernamental</span>
            </td>
            <td colspan="2" style="font-size: 10px;">Si es Comerciante indicar Ramo: <?= $data['proposed_affiliate_commercial_branch'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;"><br>
            Descripción de la Actividad:<br>
            <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_activity_description'] ?? '') == 'Independiente' ? '☑' : '☐' ?></span> Independiente</span>
            <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_activity_description'] ?? '') == 'Dependiente' ? '☑' : '☐' ?></span> Dependiente</span>
            <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_activity_description'] ?? '') == 'Societaria' ? '☑' : '☐' ?></span> Societaria</span>
            </td>
        </tr>
        <tr>
            <td colspan="8" style="font-size: 10px;">
                Ingreso Anual Bs:<br>
                <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_annual_income'] ?? '') == 'De 1 a 5 Salarios mínimos' ? '☑' : '☐' ?></span> De 1 a 5</span>
                <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_annual_income'] ?? '') == 'De 6 a 10 Salarios mínimos' ? '☑' : '☐' ?></span> De 6 a 10</span>
                <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_annual_income'] ?? '') == 'De 11 a 20 Salarios mínimos' ? '☑' : '☐' ?></span> De 11 a 20</span>
                <span class="checkbox-group"><span class="checkbox"><?= ($data['contracting_party_annual_income'] ?? '') == 'De 20 Salarios mínimos en adelante' ? '☑' : '☐' ?></span> 20+</span>
            </td>
        </tr>
        <tr>
            <td colspan="6" style="font-size: 10px;">Dirección de Residencia: <?= $data['contracting_party_residence_address'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Teléfono Residencia: <?= $data['contracting_party_phone_residence'] ?? '' ?></td>
        </tr>
        <tr>
            <td colspan="6" style="font-size: 10px;">Dirección de Oficina: <?= $data['contracting_party_office_address'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Teléfono Oficina: <?= $data['contracting_party_phone_office'] ?? '' ?></td>
        </tr>
        <tr>
            <td colspan="8" style="font-size: 10px;">Dirección de Cobro: <?= $data['contracting_party_office_address'] ?? '' ?></td>
        </tr>
        <tr>
            <td colspan="3" style="font-size: 10px;">Teléfono Celular: <?= $data['contracting_party_cell_phone'] ?? '' ?></td>
            <td colspan="5" style="font-size: 10px;">Correo Electrónico: <?= $data['contracting_party_email'] ?? '' ?></td>
        </tr>
        <?php if ($data['has_corporate_relation'] ?? false): ?>
        <tr>
            <td colspan="8" style="font-size: 10px;"><b>En caso de ser Persona Jurídica, Datos Corporativos:</b></td>
        </tr>
         <tr>
            <td colspan="2" style="font-size: 10px;">Razón Social: <?= $data['corporate_name'] ?? '' ?></td>
            <td colspan="1" style="font-size: 10px;">R.I.F.: <?= $data['corporate_rif'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Nº de Registro Mercantil: <?= $data['corporate_mercantile_register'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Nº de Tomo: <?= $data['corporate_tome'] ?? '' ?></td>
            <td colspan="1" style="font-size: 10px;">F/Registro: <?= isset($data['corporate_registration_date']) && $data['corporate_registration_date'] ? date('d/m/Y', strtotime($data['corporate_registration_date'])) : '' ?></td>
        </tr>
        <tr>
            <td colspan="8" style="font-size: 10px;">
                Actividad Económica: <?= $data['corporate_economic_activity'] ?? '' ?>
            </td>
        </tr>
        <tr>
            <td colspan="6" style="font-size: 10px;">Dirección: <?= $data['corporate_address'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Teléfono: <?= $data['corporate_phone'] ?? '' ?></td>
        </tr>
        <tr>
            <td colspan="8" style="font-size: 10px;">
                Productos y Servicios que ofrece: <?= $data['corporate_products_services'] ?? '' ?>
            </td>
        </tr>
        <tr>
            <td colspan="4" style="font-size: 10px;">Utilidad del ejercicio económico inmediatamente anterior, cuando aplique: <?= $data['corporate_profit'] ?? '' ?></td>
            <td colspan="4" style="font-size: 10px;">Patrimonio, según último estado de resultados o estado de ganancias y pérdidas: <?= $data['corporate_equity'] ?? '' ?></td>
        </tr>
        <tr>
            <td colspan="8" style="font-size: 10px;"><b>Datos del Representante Legal:</b></td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 10px;">Nombre y Apellido: <?= $data['legal_representative_name'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">C.I. / R.I.F./ Pasaporte: <?= $data['legal_representative_ci'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Nacionalidad: <?= $data['legal_representative_nationality'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Estado civil: <?= $data['legal_representative_marital_status'] ?? '' ?></td>
        </tr>
        <tr>
            <td colspan="1" style="font-size: 10px;">Lugar Nacimiento: <?= $data['legal_representative_birthplace'] ?? '' ?></td>
            <td colspan="1" style="font-size: 10px;">Fecha Nacimiento: <?= isset($data['legal_representative_birthdate']) && $data['legal_representative_birthdate'] ? date('d/m/Y', strtotime($data['legal_representative_birthdate'])) : '' ?></td>
            <td colspan="2" style="font-size: 10px;">Sexo: <?= $data['legal_representative_sex'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Profesión: <?= $data['legal_representative_profession'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Ocupación: <?= $data['legal_representative_occupation'] ?? '' ?></td>
        </tr>
        <tr>
            <td colspan="4" style="font-size: 10px;">
            Descripción de la Actividad: <?= $data['legal_representative_activity_description'] ?? '' ?>
            </td>
            <td colspan="2" style="font-size: 10px;">Dirección: <?= $data['legal_representative_address'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Teléfono: <?= $data['legal_representative_phone'] ?? '' ?></td>
        </tr>
        <?php endif; ?>

    </table>

    <?php if ($data['has_corporate_relation'] ?? false): ?>
    <table class="family-table">
        <thead>
            <tr>
                <th colspan="8">DATOS DEL CORPORATIVO</th>
            </tr>
        </thead>
        <tr>
            <td colspan="2" style="font-size: 10px;">Razón Social: <?= $data['corporate_name'] ?? '' ?><br></td>
            <td colspan="1" style="font-size: 10px;">R.I.F.: <?= $data['corporate_rif'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Nº de Registro Mercantil: <?= $data['corporate_mercantile_register'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Nº de Tomo: <?= $data['corporate_mercantile_register'] ?? '' ?></td>
            <td colspan="1" style="font-size: 10px;">F/Registro: <?= $data['corporate_registration_date'] ?? '' ?></td>
        </tr>
        <tr>
            <td colspan="8" style="font-size: 10px;">
                Actividad Económica: <?= $data['corporate_economic_activity'] ?? '' ?>
            </td>
        </tr>
        <tr>
            <td colspan="6" style="font-size: 10px;">Dirección: <?= $data['corporate_address'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Teléfono: <?= $data['corporate_phone'] ?? '' ?></td>
        </tr>
        <tr>
            <td colspan="8" style="font-size: 10px;">
                Productos y Servicios que ofrece: <?= $data['corporate_products_services'] ?? '' ?>
            </td>
        </tr>
        <tr>
            <td colspan="4" style="font-size: 10px;">Utilidad del ejercicio económico inmediatamente anterior: <?= $data['corporate_profit'] ?? '' ?></td>
            <td colspan="4" style="font-size: 10px;">Patrimonio: <?= $data['corporate_equity'] ?? '' ?></td>
        </tr>
        <tr>
            <td colspan="8" style="font-size: 10px;"><b>Datos del Representante Legal del Corporativo:</b></td>
        </tr>
        <tr>
            <td colspan="2" style="font-size: 10px;">Nombre y Apellido: <?= $data['corporate_legal_representative_name'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">C.I.: <?= $data['corporate_legal_representative_ci'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Nacionalidad: <?= $data['corporate_legal_representative_nationality'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Estado civil: <?= $data['corporate_legal_representative_marital_status'] ?? '' ?></td>
        </tr>
        <tr>
            <td colspan="1" style="font-size: 10px;">Lugar Nacimiento: <?= $data['corporate_legal_representative_birthplace'] ?? '' ?></td>
            <td colspan="1" style="font-size: 10px;">Fecha Nacimiento: <?= $data['corporate_legal_representative_birthdate'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Sexo: <?= $data['corporate_legal_representative_sex'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Profesión: <?= $data['corporate_legal_representative_profession'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Ocupación: <?= $data['corporate_legal_representative_occupation'] ?? '' ?></td>
        </tr>
        <tr>
            <td colspan="4" style="font-size: 10px;">
            Descripción de la Actividad: <?= $data['corporate_legal_representative_activity_description'] ?? '' ?>
            </td>
            <td colspan="2" style="font-size: 10px;">Dirección: <?= $data['corporate_legal_representative_address'] ?? '' ?></td>
            <td colspan="2" style="font-size: 10px;">Teléfono: <?= $data['corporate_legal_representative_phone'] ?? '' ?></td>
        </tr>
    </table>
    <?php endif; ?>

  <table class="family-table">
    <tr>
        <th colspan="2">PLAN SOLICITADO</th>
        <th colspan="2">Moneda</th>
        <th colspan="2">Deducible</th>
        <th colspan="2">Límite de Cobertura</th>
    </tr>
    <tr>
        <td colspan="2"><?= $data['plan_selected'] ?? '' ?></td>
        <td colspan="2"><?= $data['plan_currency'] ?? '' ?></td>
        <td colspan="2"><?= $data['plan_deductible'] ?? '' ?></td>
        <td colspan="2"><?= $data['plan_coverage_limit'] ?? '' ?></td>
    </tr>
    <tr>
        <th colspan="4">Cobertura Opcional (El Contratante no está obligado a contratar esta cobertura)</th>
        <th colspan="2">Deducible</th>
        <th colspan="2">Límite de Cobertura</th>
    </tr>
    <tr>
        <td colspan="4">
            <span class="checkbox-group"><span class="checkbox"><?= ($data['maternity_coverage'] ?? false) ? '☑' : '☐' ?></span> Servicio de Maternidad</span>
        </td>
        <td colspan="2"><?= $data['maternity_deductible'] ?? '' ?></td>
        <td colspan="2"><?= $data['maternity_coverage_limit'] ?? '' ?></td>
    </tr>
</table>


</div>

<div class="pdf-container">
    <table class="family-table">
        <thead>
            <tr>
                <th colspan="5">GRUPO FAMILIAR: (PERSONAS A ASEGURAR, ADEMÁS DEL AFILIADO TITULAR)</th>
            </tr>
            <tr>
                <th width="30%">Apellidos y Nombres</th>
                <th width="20%">Cédula de Identidad</th>
                <th width="20%">Parentesco</th>
                <th width="10%">Sexo</th>
                <th width="20%">F/Nacimiento</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($data['family_group'])): ?>
                <?php foreach ($data['family_group'] as $member): ?>
                    <tr>
                        <td class="underline"><?= htmlspecialchars($member['name'] ?? '') ?></td>
                        <td class="underline"><?= htmlspecialchars($member['ci'] ?? '') ?></td>
                        <td class="underline"><?= htmlspecialchars($member['relationship'] ?? '') ?></td>
                        <td class="underline"><?= htmlspecialchars($member['sex'] ?? '') ?></td>
                        <td class="underline"><?= isset($member['birthdate']) && $member['birthdate'] ? date('d/m/Y', strtotime($member['birthdate'])) : '' ?></td>
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

    <table class="family-table">
        <thead>
            <tr>
                <th colspan="5">BENEFICIARIO EN CASO DE MUERTE DEL PROPUESTO AFILIADO TITULAR</th>
            </tr>
            <tr>
                <th width="30%">Apellidos y Nombres</th>
                <th width="20%">Cédula de Identidad</th>
                <th width="20%">Parentesco</th>
                <th width="10%">Sexo</th>
                <th width="20%">F/Nacimiento</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="underline"><?= htmlspecialchars($data['beneficiary_name'] ?? '') ?></td>
                <td class="underline"><?= htmlspecialchars($data['beneficiary_ci'] ?? '') ?></td>
                <td class="underline"><?= htmlspecialchars($data['beneficiary_relationship'] ?? '') ?></td>
                <td class="underline"><?= htmlspecialchars($data['beneficiary_sex'] ?? '') ?></td>
                <td class="underline"><?= isset($data['beneficiary_birthdate']) && $data['beneficiary_birthdate'] ? date('d/m/Y', strtotime($data['beneficiary_birthdate'])) : '' ?></td>
            </tr>
        </tbody>
    </table>
    
</div>

    <p class="note">En caso de muerte de algún otro AFILIADO en este seguro el BENEFICIARIO es el propuesto AFILIADO TITULAR</p>

<div class=""></div>

<table class="family-table">
    <tr>
        <th colspan="8">DATOS BANCARIOS DEL CONTRATANTE</th>
    </tr>
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

<table class="family-table">
    <tbody>
        <tr>
            <td colspan="4">
                <div class="section-title" style="border-bottom: none;">DECLARACIONES Y AUTORIZACIONES</div>
                <p>Yo, <span class="underline"><?= $data['declaration_proposed_affiliate_name'] ?? '' ?></span>, titular de la cédula de identidad N° <span class="underline"><?= $data['declaration_proposed_affiliate_ci'] ?? '' ?></span>en mi carácter de PROPUESTO AFILIADO TITULAR,</p>
                <p>* Declaro que he leído cuidadosamente y totalmente, una a una, todas las preguntas y respuestas consignadas en esta solicitud de seguro y que ellas son verdaderas, amplias, completas y exactas.</p>
                <p>* Declaro que el correo electrónico suministrado me pertenece e identifica plenamente, por lo que autorizo expresamente a Sistema Integral de Salud Programado Medicina Prepagada, S.A. para enviarme todos los documentos que forman parte del contrato y cualquier comunicación pertinente por este medio.</p>

                <p>Yo, <span class="underline"><?= $data['declaration_contracting_party_name'] ?? '' ?></span>, titular de la cédula de identidad N° <span class="underline"><?= $data['declaration_contracting_party_ci'] ?? '' ?></span>en mi carácter de CONTRATANTE,</p>
                <p>* Doy fe de que el dinero utilizado para el pago de las cuotas, provienen de una fuente lícita y su origen no guarda relación alguna con capitales, bienes, haberes, valores, títulos u operaciones, producto de actividades ilícitas o que provenga de los delitos de Delincuencia Organizada u otras conductas tipificadas en la legislación venezolana.</p>
                <p>* Autorizo a Sistema Integral de Salud Programado Medicina Prepagada, S.A. a debitar de la Cuenta Bancaria / o cargar en la Tarjeta de Crédito, cuyos datos proporciono en esta solicitud, los cobros de cuotas de este servicio de medicina prepagada durante su vigencia a partir de su emisión. Y me comprometo a mantener el monto suficiente para cumplir con la obligación del pago de la cuota correspondiente.</p>
            </td>
        </tr>
        <tr>
            <td colspan="4">
                
                <img src="<?= Html::encode($firmas) ?>" alt="firmas" style="width: 100%;">
                <p class="approval">Aprobado por la Superintendencia de la Actividad Aseguradora según Providencia Nº SAA-SUT-34169 de fecha 13/02/2025</p>
            </td>
        </tr>
    </tbody>
</table>