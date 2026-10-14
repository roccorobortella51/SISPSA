<!-- ============================================================ -->
<!-- PORTADA OFICIAL                                              -->
<!-- ============================================================ -->
<div style="text-align: center; padding: 40px 20px 30px 20px; border-bottom: 3px solid #0078d4; margin-bottom: 30px;">

<img src="sispsalogo.jpg" alt="Logo SISPSA" style="width: 160px; height: auto; margin-bottom: 25px;">

<div style="font-size: 11px; letter-spacing: 3px; color: #605e5c; font-weight: 600; text-transform: uppercase; margin-bottom: 15px;">
Sistema Integral de Salud Programado Medicina Prepagada S.A.
</div>

<h1 style="font-size: 32px; font-weight: 700; color: #1a3a6e; margin: 15px 0 10px 0; letter-spacing: -0.8px; line-height: 1.2;">
Informe de Avances Tecnológicos del Sistema
</h1>

<div style="font-size: 16px; font-weight: 600; color: #323130; margin: 15px 0 5px 0;">
SISPSA — Plataforma Tecnológica de Gestión
</div>

<div style="font-size: 14px; font-style: italic; color: #605e5c; margin: 5px 0 0 0;">
Caracas, 23 de Septiembre del 2026
</div>
</div>

<p style="text-align: justify; line-height: 1.8; margin-bottom: 16px; color: #323130; font-size: 13px;">,
El presente informe tiene por finalidad el cumplimiento de las disposiciones emanadas de la Superintendencia de la Actividad Aseguradora y en atención a los requerimientos de información sobre la plataforma tecnológica que soporta la operación de la entidad, se presenta el informe ejecutivo sobre el Sistema Integral de Salud Programado (SISPSA).
</p>

<p style="text-align: justify; line-height: 1.8; margin-bottom: 16px; color: #323130; font-size: 13px;">
Este documento describe, de manera clara y organizada, el alcance funcional, las capacidades operativas, los controles internos y la arquitectura de gobernanza del sistema que administra la totalidad de los procesos comerciales, clínicos y financieros de la organización. El contenido está diseñado para facilitar la comprensión de la operación del sistema por parte de los órganos reguladores y de las partes interesadas autorizadas.
</p>

<p style="text-align: justify; line-height: 1.8; margin-bottom: 16px; color: #323130; font-size: 13px;">
El sistema SISPSA constituye el registro único y autorizado de todas las operaciones de la entidad y es la fuente oficial de información para los reportes regulatorios exigidos por este Despacho.
</p>

<hr style="border: none; border-top: 2px solid #e1dfdd; margin: 30px 0;">

<!-- ============================================================ -->
<!-- SECCIÓN 1: PROPÓSITO Y CONTEXTO                               -->
<!-- ============================================================ -->
<h2 style="color: #1a3a6e; font-size: 20px; font-weight: 700; border-bottom: 3px solid #0078d4; padding-bottom: 10px; margin: 40px 0 20px 0; letter-spacing: -0.3px;">
1. Propósito y Contexto del Negocio
</h2>

<p style="text-align: justify; line-height: 1.8; margin-bottom: 16px; color: #323130; font-size: 13px;">
SISPSA es la plataforma tecnológica empresarial que sostiene la operación integral del negocio de medicina prepagada de la organización. Constituye el sistema único, autorizado y auditable de registro para las siguientes áreas de la operación:
</p>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 13px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
  <thead>
    <tr style="background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%); color: #ffffff;">
      <th style="padding: 14px 18px; text-align: left; font-weight: 600; font-size: 12px; letter-spacing: 0.8px; text-transform: uppercase; border: none; width: 30%;">Área de Operación</th>
      <th style="padding: 14px 18px; text-align: left; font-weight: 600; font-size: 12px; letter-spacing: 0.8px; text-transform: uppercase; border: none;">Alcance Funcional</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background: #ffffff;">
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Comercial</td>
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Incorporación de clínicas prestadoras, diseño y administración de planes de seguro, y gestión de la red de agencias y agentes comerciales.</td>
    </tr>
    <tr style="background: #f7f9fc;">
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Afiliación</td>
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Registro y administración de afiliados (individuales, corporativos y por carga masiva), emisión de contratos y facturación recurrente.</td>
    </tr>
    <tr style="background: #ffffff;">
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Clínico</td>
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Registro de atenciones médicas y citas, verificación de la cobertura contratada y mantenimiento del historial de salud del afiliado.</td>
    </tr>
    <tr style="background: #f7f9fc;">
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Financiero</td>
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Cobranza, conciliación de pagos, emisión de recibos, cálculo de comisiones y generación de reportes regulatorios.</td>
    </tr>
  </tbody>
</table>

<p style="text-align: justify; line-height: 1.8; margin-bottom: 16px; color: #323130; font-size: 13px;">
El sistema sustituye procesos manuales y documentos físicos por una plataforma centralizada, trazable y escalable que presta servicio a múltiples clínicas, agencias y clientes corporativos de manera simultánea, con estrictos controles de acceso y de integridad de la información.
</p>

<hr style="border: none; border-top: 2px solid #e1dfdd; margin: 30px 0;">

<!-- ============================================================ -->
<!-- SECCIÓN 2: BASE TECNOLÓGICA                                   -->
<!-- ============================================================ -->
<h2 style="color: #1a3a6e; font-size: 20px; font-weight: 700; border-bottom: 3px solid #0078d4; padding-bottom: 10px; margin: 40px 0 20px 0; letter-spacing: -0.3px;">
2. Base Tecnológica
</h2>

<p style="text-align: justify; line-height: 1.8; margin-bottom: 16px; color: #323130; font-size: 13px;">
La plataforma está construida sobre tecnologías de uso extendido en el sector asegurador, seleccionadas por su estabilidad, seguridad y capacidad de auditoría:
</p>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 13px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
  <thead>
    <tr style="background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%); color: #ffffff;">
      <th style="padding: 14px 18px; text-align: left; font-weight: 600; font-size: 12px; letter-spacing: 0.8px; text-transform: uppercase; border: none; width: 30%;">Capa</th>
      <th style="padding: 14px 18px; text-align: left; font-weight: 600; font-size: 12px; letter-spacing: 0.8px; text-transform: uppercase; border: none;">Implementación</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background: #ffffff;">
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Plataforma de Aplicación</td>
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Sistema de gestión empresarial de arquitectura modular, con separación clara entre la lógica de presentación, la lógica de negocio y el acceso a datos.</td>
    </tr>
    <tr style="background: #f7f9fc;">
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Base de Datos</td>
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Motor de base de datos relacional de grado empresarial, con más de cuarenta (40) entidades principales debidamente normalizadas.</td>
    </tr>
    <tr style="background: #ffffff;">
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Interfaz de Usuario</td>
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Interfaz web moderna, accesible desde navegador, con diseño responsivo y componentes de usabilidad inspirados en estándares corporativos internacionales.</td>
    </tr>
    <tr style="background: #f7f9fc;">
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Almacenamiento de Archivos</td>
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Servicio de almacenamiento seguro en la nube, con claves de acceso generadas de forma única para cada documento.</td>
    </tr>
    <tr style="background: #ffffff;">
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Notificaciones Electrónicas</td>
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Servicio de correo electrónico corporativo con protocolos de seguridad para envío de confirmaciones, recibos y notificaciones al afiliado.</td>
    </tr>
    <tr style="background: #f7f9fc;">
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Generación de Documentos</td>
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Módulos de emisión de documentos en formato PDF y hojas de cálculo, con formatos alineados a los requisitos regulatorios vigentes.</td>
    </tr>
    <tr style="background: #ffffff;">
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Fuentes de Información Externa</td>
      <td style="padding: 12px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Integración autorizada con el Banco Central de Venezuela para la obtención de la tasa de cambio oficial, con mecanismos automáticos de respaldo.</td>
    </tr>
  </tbody>
</table>

<div style="
    border-left: 5px solid #107c10;
    background: linear-gradient(135deg, #f3faf3 0%, #e8f5e8 100%);
    padding: 18px 24px;
    margin: 24px 0;
    border-radius: 0 8px 8px 0;
    box-shadow: 0 2px 8px rgba(16, 124, 16, 0.08);
">
<div style="font-weight: 700; color: #107c10; font-size: 13px; margin-bottom: 8px; letter-spacing: 0.3px; text-transform: uppercase;">
✓ Principios Rectores
</div>
<p style="margin: 0 0 10px 0; line-height: 1.7; color: #323130; font-size: 13px;">
La plataforma ha sido diseñada bajo tres principios fundamentales:
</p>
<ul style="margin: 0; padding-left: 20px; line-height: 1.8; color: #323130; font-size: 13px;">
  <li><strong style="color: #107c10;">Seguridad:</strong> control de acceso basado en roles en cada operación.</li>
  <li><strong style="color: #107c10;">Confiabilidad:</strong> operaciones transaccionales y trazabilidad de cambios.</li>
  <li><strong style="color: #107c10;">Auditabilidad:</strong> registro íntegro de cada transacción relevante.</li>
</ul>
</div>

<hr style="border: none; border-top: 2px solid #e1dfdd; margin: 30px 0;">

<!-- ============================================================ -->
<!-- SECCIÓN 3: MÓDULOS FUNCIONALES                                -->
<!-- ============================================================ -->
<h2 style="color: #1a3a6e; font-size: 20px; font-weight: 700; border-bottom: 3px solid #0078d4; padding-bottom: 10px; margin: 40px 0 20px 0; letter-spacing: -0.3px;">
3. Módulos Funcionales del Sistema
</h2>

<p style="text-align: justify; line-height: 1.8; margin-bottom: 16px; color: #323130; font-size: 13px;">
La plataforma está organizada en <strong>veinte (20) módulos funcionales</strong> que en su conjunto cubren la totalidad de la operación de la entidad. A continuación se describen las capacidades principales de cada área.
</p>

<h3 style="color: #1a3a6e; font-size: 15px; font-weight: 700; margin: 28px 0 14px 0; padding: 10px 16px; background: #f7f9fc; border-radius: 6px; border-left: 4px solid #0078d4;">
3.1 Área Comercial y de Producto
</h3>

<ul style="line-height: 1.8; padding-left: 24px; margin-bottom: 20px; color: #323130; font-size: 13px;">
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Gestión de Clínicas Prestadoras:</strong> Registro y administración de clínicas afiliadas a la red, incluyendo su ubicación geográfica completa, metas mensuales de captación, tableros de indicadores operativos y reportes de intermediarios asociados.</li>
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Planes de Seguro:</strong> Diseño y administración de planes de medicina prepagada, ensamblados a partir de servicios médicos individuales, cada uno con sus propios límites de uso y plazos de espera.</li>
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Catálogo de Servicios Médicos (Baremos):</strong> Registro valorado de servicios médicos por clínica, con capacidad de carga masiva desde hojas de cálculo y mecanismos automáticos de prevención de duplicidad.</li>
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Clasificación de Servicios:</strong> Organización de servicios por áreas operativas (Emergencia, Cirugía, Consultas, Laboratorio, entre otras).</li>
</ul>

<h3 style="color: #1a3a6e; font-size: 15px; font-weight: 700; margin: 28px 0 14px 0; padding: 10px 16px; background: #f7f9fc; border-radius: 6px; border-left: 4px solid #0078d4;">
3.2 Área de Afiliación y Contratos
</h3>

<ul style="line-height: 1.8; padding-left: 24px; margin-bottom: 20px; color: #323130; font-size: 13px;">
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Registro de Afiliados:</strong> Captura integral de datos personales, dirección de residencia, datos del contratante, información bancaria, grupo familiar, documentación de identidad y fotografía del afiliado, con generación automática del contrato y de las doce (12) cuotas mensuales correspondientes.</li>
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Afiliados Corporativos:</strong> Administración de entidades corporativas y carga masiva de sus empleados mediante archivos estructurados.</li>
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Contratos:</strong> Administración del ciclo de vida completo del contrato, con reglas automáticas de transición de estatus según las condiciones comerciales y financieras.</li>
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Cuotas:</strong> Administración de cuotas mensuales con período de gracia de siete (7) días, actualización diaria automática de estatus y soporte para facturación anticipada.</li>
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Dependientes y Declaraciones de Salud:</strong> Administración de la relación entre el titular y sus dependientes, registro de pre-existencias médicas y declaraciones formales de salud.</li>
</ul>

<h3 style="color: #1a3a6e; font-size: 15px; font-weight: 700; margin: 28px 0 14px 0; padding: 10px 16px; background: #f7f9fc; border-radius: 6px; border-left: 4px solid #0078d4;">
3.3 Área de Operaciones Clínicas
</h3>

<ul style="line-height: 1.8; padding-left: 24px; margin-bottom: 20px; color: #323130; font-size: 13px;">
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Atenciones y Citas Médicas:</strong> Registro de atenciones médicas y citas programadas, con verificación automática de la cobertura contratada en tres niveles simultáneos: plazo de espera, límite de uso por servicio y tope monetario.</li>
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Pre-existencias:</strong> Seguimiento individualizado por afiliado, con ciclo de vida de estatus y capacidad de emisión en formato PDF.</li>
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Contactos de Emergencia:</strong> Registro de datos de contacto de emergencia asociados a cada afiliado.</li>
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Listas de Verificación de Clínicas:</strong> Formularios de verificación operativa periódica sobre las clínicas prestadoras.</li>
</ul>

<h3 style="color: #1a3a6e; font-size: 15px; font-weight: 700; margin: 28px 0 14px 0; padding: 10px 16px; background: #f7f9fc; border-radius: 6px; border-left: 4px solid #0078d4;">
3.4 Área Financiera
</h3>

<ul style="line-height: 1.8; padding-left: 24px; margin-bottom: 20px; color: #323130; font-size: 13px;">
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Pagos:</strong> Registro de pagos bajo cinco (5) modalidades: efectivo en dólares, pago móvil, punto de venta, transferencia bancaria y Zelle, con carga de comprobante y flujo formal de conciliación.</li>
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Recibos:</strong> Emisión de un recibo por cada cuota pagada, con entrega al afiliado mediante documento PDF y correo electrónico.</li>
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Comisiones:</strong> Cálculo automático de comisiones según el modelo de distribución vigente: Asesor (10%), Agencia (4%) y Clínica (70%).</li>
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Tasa de Cambio:</strong> Obtención automática de la tasa oficial del Banco Central de Venezuela, con mecanismos de respaldo para garantizar la continuidad operativa.</li>
</ul>

<h3 style="color: #1a3a6e; font-size: 15px; font-weight: 700; margin: 28px 0 14px 0; padding: 10px 16px; background: #f7f9fc; border-radius: 6px; border-left: 4px solid #0078d4;">
3.5 Área de Fuerza de Venta y Agencias
</h3>

<ul style="line-height: 1.8; padding-left: 24px; margin-bottom: 20px; color: #323130; font-size: 13px;">
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Agencias:</strong> Administración de entidades comerciales, con su propia estructura de comisiones y jerarquía.</li>
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Asesores e Intermediarios:</strong> Administración de agentes comerciales bajo cada agencia, con permisos granulares según su rol operativo (venta, asesoría, cobranza, post-venta y registro).</li>
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Tableros de Desempeño:</strong> Indicadores en tiempo real sobre la actuación de la fuerza de venta, con alertas operativas y detección de inactividad.</li>
</ul>

<h3 style="color: #1a3a6e; font-size: 15px; font-weight: 700; margin: 28px 0 14px 0; padding: 10px 16px; background: #f7f9fc; border-radius: 6px; border-left: 4px solid #0078d4;">
3.6 Área de Reportes y Analítica
</h3>

<ul style="line-height: 1.8; padding-left: 24px; margin-bottom: 20px; color: #323130; font-size: 13px;">
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Reportes Operativos:</strong> Atenciones médicas por clínica, uso de servicios, cartera vigente y bitácora de eventos.</li>
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Reportes Financieros:</strong> Reportes de pagos bajo el formato regulatorio <strong>SUDEASEG-002</strong>, reportes de comisiones y reportes de afiliados activos por mes, con análisis de tendencias de crecimiento.</li>
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Reportes Regulatorios:</strong> Reporte de Transacciones y Operaciones de Negocios (<strong>RTON</strong>), diseñado conforme a las disposiciones aplicables.</li>
  <li style="margin-bottom: 10px;"><strong style="color: #1a3a6e;">Tableros Ejecutivos:</strong> Tableros diferenciados por rol, con la información gerencial pertinente a cada nivel de la organización.</li>
</ul>

<hr style="border: none; border-top: 2px solid #e1dfdd; margin: 30px 0;">

<!-- ============================================================ -->
<!-- SECCIÓN 4: ROLES DE USUARIO                                   -->
<!-- ============================================================ -->
<h2 style="color: #1a3a6e; font-size: 20px; font-weight: 700; border-bottom: 3px solid #0078d4; padding-bottom: 10px; margin: 40px 0 20px 0; letter-spacing: -0.3px;">
4. Roles de Usuario y Control de Acceso
</h2>

<p style="text-align: justify; line-height: 1.8; margin-bottom: 16px; color: #323130; font-size: 13px;">
El sistema aplica un modelo formal de control de acceso basado en roles (<strong>RBAC — Role-Based Access Control</strong>), conforme a las mejores prácticas del sector. Actualmente se reconocen <strong>trece (13) roles operativos</strong>, cada uno con permisos específicos y alcance limitado a las funciones propias de su responsabilidad:
</p>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 13px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
  <thead>
    <tr style="background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%); color: #ffffff;">
      <th style="padding: 14px 18px; text-align: left; font-weight: 600; font-size: 12px; letter-spacing: 0.8px; text-transform: uppercase; border: none; width: 38%;">Rol</th>
      <th style="padding: 14px 18px; text-align: left; font-weight: 600; font-size: 12px; letter-spacing: 0.8px; text-transform: uppercase; border: none;">Responsabilidad Principal</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background: #ffffff;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Superadministrador / Administrador</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Control total del sistema y de su configuración.</td>
    </tr>
    <tr style="background: #f7f9fc;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Director de Comercialización</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Supervisión comercial global.</td>
    </tr>
    <tr style="background: #ffffff;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Gerente de Clínica</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Indicadores y operación integral de la clínica asignada.</td>
    </tr>
    <tr style="background: #f7f9fc;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Coordinador de Clínica</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Operación diaria de la clínica asignada.</td>
    </tr>
    <tr style="background: #ffffff;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Gerente de Operaciones</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Supervisión operativa de procesos transversales.</td>
    </tr>
    <tr style="background: #f7f9fc;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Finanzas</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Gestión de ingresos, contratos y conciliación de pagos.</td>
    </tr>
    <tr style="background: #ffffff;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Agente</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Gestión de agencia comercial y su fuerza de venta.</td>
    </tr>
    <tr style="background: #f7f9fc;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Asesor</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Administración de cartera personal de afiliados.</td>
    </tr>
    <tr style="background: #ffffff;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Admisión / Atención / Control de Citas</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Registro de pacientes y agenda de servicios médicos.</td>
    </tr>
    <tr style="background: #f7f9fc;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Administrador de Clínica / Operaciones-Bosque</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Administración a nivel clínica.</td>
    </tr>
    <tr style="background: #ffffff;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Afiliado</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Acceso a su información personal y de contrato.</td>
    </tr>
  </tbody>
</table>

<div style="
    border-left: 5px solid #e97c00;
    background: linear-gradient(135deg, #fff8e8 0%, #fff2cc 100%);
    padding: 18px 24px;
    margin: 24px 0;
    border-radius: 0 8px 8px 0;
    box-shadow: 0 2px 8px rgba(233, 124, 0, 0.08);
">
<div style="font-weight: 700; color: #b25c00; font-size: 13px; margin-bottom: 10px; letter-spacing: 0.3px; text-transform: uppercase;">
⚠ Permisología de Acceso Implementadas
</div>
<ul style="margin: 0; padding-left: 20px; line-height: 1.8; color: #323130; font-size: 13px;">
  <li style="margin-bottom: 6px;">Filtro de control de acceso aplicado de manera global a todas las operaciones del sistema.</li>
  <li style="margin-bottom: 6px;">Restricción automática de las consultas a la información de la clínica asignada para roles de ámbito clínico.</li>
  <li style="margin-bottom: 6px;">Anulación de contratos restringida exclusivamente a cargos gerenciales y de supervisión autorizados.</li>
  <li style="margin-bottom: 6px;">Eliminación de registros médicos sujeta a verificación de rol, exigencia de motivo expreso y registro obligatorio en bitácora de auditoría.</li>
</ul>
</div>

<hr style="border: none; border-top: 2px solid #e1dfdd; margin: 30px 0;">

<!-- ============================================================ -->
<!-- SECCIÓN 5: FLUJOS DE TRABAJO PRINCIPALES                      -->
<!-- ============================================================ -->
<h2 style="color: #1a3a6e; font-size: 20px; font-weight: 700; border-bottom: 3px solid #0078d4; padding-bottom: 10px; margin: 40px 0 20px 0; letter-spacing: -0.3px;">
5. Flujos de Trabajo Principales
</h2>

<p style="text-align: justify; line-height: 1.8; margin-bottom: 20px; color: #323130; font-size: 13px;">
El sistema automatiza los siguientes flujos operativos de la entidad, sustituyendo tareas manuales por procesos controlados, trazables y verificables:
</p>

<div style="margin-bottom: 16px; padding: 16px 22px; background: #f7f9fc; border-radius: 8px; border-left: 4px solid #0078d4; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
<p style="margin: 0; line-height: 1.8; color: #323130; font-size: 13px;"><strong style="color: #0078d4; font-size: 14px;">1.</strong> <strong style="color: #1a3a6e;">Registro de Afiliado Individual:</strong> Un procedimiento único ejecuta de forma coordinada la creación del perfil del afiliado, la emisión de sus credenciales de acceso, la generación del contrato, el establecimiento de las doce (12) cuotas mensuales, la asignación del rol correspondiente y la confirmación por correo electrónico al afiliado.</p>
</div>

<div style="margin-bottom: 16px; padding: 16px 22px; background: #f7f9fc; border-radius: 8px; border-left: 4px solid #0078d4; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
<p style="margin: 0; line-height: 1.8; color: #323130; font-size: 13px;"><strong style="color: #0078d4; font-size: 14px;">2.</strong> <strong style="color: #1a3a6e;">Afiliación Masiva de Empleados:</strong> El sistema procesa los archivos de carga masiva bajo el principio de aislamiento transaccional por cada registro, garantizando que un error en una fila no afecte la integridad de las filas correctamente procesadas. Los resultados son presentados en un reporte de resumen que detalla los registros exitosos y los errores detectados.</p>
</div>

<div style="margin-bottom: 16px; padding: 16px 22px; background: #f7f9fc; border-radius: 8px; border-left: 4px solid #0078d4; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
<p style="margin: 0; line-height: 1.8; color: #323130; font-size: 13px;"><strong style="color: #0078d4; font-size: 14px;">3.</strong> <strong style="color: #1a3a6e;">Registro de Pago y Conciliación:</strong> El registro de un pago activa de forma coordinada la actualización de las cuotas correspondientes, la emisión de los recibos y el envío del correo electrónico de confirmación al afiliado. La conciliación posterior, realizada por el área de Finanzas, actualiza automáticamente el estatus de los contratos afectados y la condición de solvencia del afiliado.</p>
</div>

<div style="margin-bottom: 16px; padding: 16px 22px; background: #f7f9fc; border-radius: 8px; border-left: 4px solid #0078d4; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
<p style="margin: 0; line-height: 1.8; color: #323130; font-size: 13px;"><strong style="color: #0078d4; font-size: 14px;">4.</strong> <strong style="color: #1a3a6e;">Distribución de Pagos Corporativos:</strong> Los pagos realizados por clientes corporativos son distribuidos automáticamente entre los afiliados correspondientes, actualizando cada cuota y reevaluando el estatus de cada contrato involucrado.</p>
</div>

<div style="margin-bottom: 16px; padding: 16px 22px; background: #f7f9fc; border-radius: 8px; border-left: 4px solid #0078d4; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
<p style="margin: 0; line-height: 1.8; color: #323130; font-size: 13px;"><strong style="color: #0078d4; font-size: 14px;">5.</strong> <strong style="color: #1a3a6e;">Atención Médica con Verificación de Cobertura:</strong> Toda atención médica está sujeta a una verificación simultánea de tres condiciones: vigencia del contrato, cumplimiento del plazo de espera aplicable y disponibilidad de cobertura conforme al límite de uso y al tope monetario del plan contratado.</p>
</div>

<div style="margin-bottom: 16px; padding: 16px 22px; background: #f7f9fc; border-radius: 8px; border-left: 4px solid #0078d4; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
<p style="margin: 0; line-height: 1.8; color: #323130; font-size: 13px;"><strong style="color: #0078d4; font-size: 14px;">6.</strong> <strong style="color: #1a3a6e;">Ciclo de Vida de la Cita Médica:</strong> Las citas médicas transitan por un ciclo formal que contempla su programación, cancelación (con o sin penalidad según el plazo establecido) o su atención efectiva. El sistema emite notificaciones automáticas al afiliado en los momentos clave del ciclo.</p>
</div>

<div style="margin-bottom: 16px; padding: 16px 22px; background: #f7f9fc; border-radius: 8px; border-left: 4px solid #0078d4; box-shadow: 0 1px 3px rgba(0,0,0,0.04);">
<p style="margin: 0; line-height: 1.8; color: #323130; font-size: 13px;"><strong style="color: #0078d4; font-size: 14px;">7.</strong> <strong style="color: #1a3a6e;">Anulación de Contrato:</strong> La anulación de contratos está restringida por rol, requiere justificación expresa y se ejecuta bajo un mecanismo transaccional que garantiza la integridad de la operación. Las cuotas pendientes son canceladas en su totalidad, y el contrato queda inhabilitado para operaciones posteriores.</p>
</div>

<hr style="border: none; border-top: 2px solid #e1dfdd; margin: 30px 0;">

<!-- ============================================================ -->
<!-- SECCIÓN 6: GOBERNANZA FINANCIERA                              -->
<!-- ============================================================ -->
<h2 style="color: #1a3a6e; font-size: 20px; font-weight: 700; border-bottom: 3px solid #0078d4; padding-bottom: 10px; margin: 40px 0 20px 0; letter-spacing: -0.3px;">
6. Gobernanza Financiera
</h2>

<p style="text-align: justify; line-height: 1.8; margin-bottom: 20px; color: #323130; font-size: 13px;">
El sistema incorpora los controles financieros requeridos para la operación de la entidad en el marco regulatorio vigente:
</p>

<div style="margin-bottom: 14px; padding: 16px 22px; background: linear-gradient(135deg, #f0f9ff 0%, #e6f4fc 100%); border-radius: 8px; border-left: 4px solid #0078d4; box-shadow: 0 1px 3px rgba(0,120,212,0.06);">
<p style="margin: 0; line-height: 1.8; color: #323130; font-size: 13px;"><strong style="color: #0078d4;">▸ Operación Multimoneda:</strong> Soporte para operaciones en dólares de los Estados Unidos de América (USD) y bolívares (Bs.), con captura individual de la tasa de cambio aplicable a cada transacción, garantizando la trazabilidad y auditabilidad de las conversiones.</p>
</div>

<div style="margin-bottom: 14px; padding: 16px 22px; background: linear-gradient(135deg, #f0f9ff 0%, #e6f4fc 100%); border-radius: 8px; border-left: 4px solid #0078d4; box-shadow: 0 1px 3px rgba(0,120,212,0.06);">
<p style="margin: 0; line-height: 1.8; color: #323130; font-size: 13px;"><strong style="color: #0078d4;">▸ Modelo de Comisiones:</strong> Aplicación automática del modelo de distribución vigente — Asesor (10%), Agencia (4%) y Clínica (70%) — con capacidad de generación de reportes de comisiones auditables.</p>
</div>

<div style="margin-bottom: 14px; padding: 16px 22px; background: linear-gradient(135deg, #f0f9ff 0%, #e6f4fc 100%); border-radius: 8px; border-left: 4px solid #0078d4; box-shadow: 0 1px 3px rgba(0,120,212,0.06);">
<p style="margin: 0; line-height: 1.8; color: #323130; font-size: 13px;"><strong style="color: #0078d4;">▸ Reportes Regulatorios:</strong> Generación de los reportes exigidos por este Despacho, incluyendo el formato de pagos <strong>SUDEASEG-002</strong>, el Reporte de Transacciones y Operaciones de Negocios (<strong>RTON</strong>) y los reportes de comisiones, en cumplimiento con las disposiciones aplicables.</p>
</div>

<div style="margin-bottom: 14px; padding: 16px 22px; background: linear-gradient(135deg, #f0f9ff 0%, #e6f4fc 100%); border-radius: 8px; border-left: 4px solid #0078d4; box-shadow: 0 1px 3px rgba(0,120,212,0.06);">
<p style="margin: 0; line-height: 1.8; color: #323130; font-size: 13px;"><strong style="color: #0078d4;">▸ Seguimiento de Solvencia:</strong> Cada afiliado cuenta con una condición formal de solvencia que es actualizada automáticamente con ocasión de los procesos de pago y conciliación, garantizando consistencia entre la situación financiera del afiliado y su acceso a los servicios contratados.</p>
</div>

<div style="margin-bottom: 14px; padding: 16px 22px; background: linear-gradient(135deg, #f0f9ff 0%, #e6f4fc 100%); border-radius: 8px; border-left: 4px solid #0078d4; box-shadow: 0 1px 3px rgba(0,120,212,0.06);">
<p style="margin: 0; line-height: 1.8; color: #323130; font-size: 13px;"><strong style="color: #0078d4;">▸ Monitor de Consumo de Fondo:</strong> Herramienta de gestión presupuestaria por clínica, con indicadores de consumo clasificados por umbrales de control, que permite la adopción oportuna de medidas operativas y financieras.</p>
</div>

<hr style="border: none; border-top: 2px solid #e1dfdd; margin: 30px 0;">

<!-- ============================================================ -->
<!-- SECCIÓN 7: CUMPLIMIENTO Y SEGURIDAD                          -->
<!-- ============================================================ -->
<h2 style="color: #1a3a6e; font-size: 20px; font-weight: 700; border-bottom: 3px solid #0078d4; padding-bottom: 10px; margin: 40px 0 20px 0; letter-spacing: -0.3px;">
7. Cumplimiento, Seguridad y Confiabilidad
</h2>

<p style="text-align: justify; line-height: 1.8; margin-bottom: 20px; color: #323130; font-size: 13px;">
El sistema ha sido diseñado y configurado con el objetivo de garantizar el cumplimiento de las disposiciones regulatorias y la protección de la información bajo su administración:
</p>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 13px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
  <thead>
    <tr style="background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%); color: #ffffff;">
      <th style="padding: 14px 18px; text-align: left; font-weight: 600; font-size: 12px; letter-spacing: 0.8px; text-transform: uppercase; border: none; width: 30%;">Área</th>
      <th style="padding: 14px 18px; text-align: left; font-weight: 600; font-size: 12px; letter-spacing: 0.8px; text-transform: uppercase; border: none;">Implementación</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background: #ffffff;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Autorización</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Control de acceso basado en roles aplicado de forma global; verificación específica de permisos en operaciones sensibles.</td>
    </tr>
    <tr style="background: #f7f9fc;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Bitácora de Auditoría</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Registro íntegro de las operaciones sensibles, incluyendo las eliminaciones de registros médicos, con trazabilidad de usuario, fecha, motivo y estado del registro afectado.</td>
    </tr>
    <tr style="background: #ffffff;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Seguridad de la Información</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Aplicación del principio de borrado lógico (soft delete) en los registros principales; uso de transacciones en operaciones de múltiples etapas para garantizar la consistencia de los datos.</td>
    </tr>
    <tr style="background: #f7f9fc;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Protección contra Fraude Electrónico</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Mecanismos de protección activos por defecto, con excepciones limitadas exclusivamente a operaciones internas debidamente autorizadas.</td>
    </tr>
    <tr style="background: #ffffff;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Seguridad de Documentos</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Almacenamiento de documentos en servicio en la nube con claves únicas por archivo; sanitización del contenido de documentos PDF para garantizar su integridad.</td>
    </tr>
    <tr style="background: #f7f9fc;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Respaldos</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Procedimiento automatizado de respaldo de la información, ejecutado de forma periódica.</td>
    </tr>
    <tr style="background: #ffffff;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; font-weight: 700; color: #1a3a6e;">Configuración Regional</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: top; color: #323130;">Aplicación de la zona horaria oficial (<code style="background: #f3f2f1; color: #d13438; padding: 2px 6px; border-radius: 3px; font-size: 12px; font-family: Consolas, monospace;">America/Caracas</code>) y configuración regional del país (<code style="background: #f3f2f1; color: #d13438; padding: 2px 6px; border-radius: 3px; font-size: 12px; font-family: Consolas, monospace;">es-VE</code>).</td>
    </tr>
  </tbody>
</table>

<hr style="border: none; border-top: 2px solid #e1dfdd; margin: 30px 0;">

<!-- ============================================================ -->
<!-- SECCIÓN 8: CAPACIDADES RECIENTES                              -->
<!-- ============================================================ -->
<h2 style="color: #1a3a6e; font-size: 20px; font-weight: 700; border-bottom: 3px solid #0078d4; padding-bottom: 10px; margin: 40px 0 20px 0; letter-spacing: -0.3px;">
8. Capacidades Recientes y Compromiso con la Mejora Continua
</h2>

<p style="text-align: justify; line-height: 1.8; margin-bottom: 20px; color: #323130; font-size: 13px;">
La organización ha mantenido una política de mejora continua sobre la plataforma, reflejada en las siguientes capacidades incorporadas recientemente:
</p>

<ul style="line-height: 1.9; padding-left: 24px; margin-bottom: 20px; color: #323130; font-size: 13px;">
  <li style="margin-bottom: 10px; padding-left: 6px;">Adopción de un sistema de diseño de interfaz alineado con los estándares corporativos internacionales, aplicado a formularios, paneles informativos, tarjetas de indicadores y mensajes al usuario.</li>
  <li style="margin-bottom: 10px; padding-left: 6px;">Incorporación de paneles deslizantes para la consulta detallada de información operativa, mejorando la experiencia de uso y la eficiencia operativa.</li>
  <li style="margin-bottom: 10px; padding-left: 6px;">Implementación de procesos de carga masiva con seguimiento de progreso en tiempo real.</li>
  <li style="margin-bottom: 10px; padding-left: 6px;">Sincronización automática de declaraciones de salud con el registro de pre-existencias médicas.</li>
  <li style="margin-bottom: 10px; padding-left: 6px;">Incorporación de un conjunto de herramientas para la migración controlada de datos históricos, con mecanismos de respaldo y reversión.</li>
  <li style="margin-bottom: 10px; padding-left: 6px;">Implementación de reportes avanzados de cartera vigente y de evolución histórica de la operación, con capacidad de exportación en formatos estándar.</li>
  <li style="margin-bottom: 10px; padding-left: 6px;">Establecimiento de una suite formal de pruebas del sistema que cubre los niveles de aceptación, funcional y unitario, garantizando la calidad y estabilidad de la plataforma.</li>
</ul>

<hr style="border: none; border-top: 2px solid #e1dfdd; margin: 30px 0;">

<!-- ============================================================ -->
<!-- SECCIÓN 9: INDICADORES                                        -->
<!-- ============================================================ -->
<h2 style="color: #1a3a6e; font-size: 20px; font-weight: 700; border-bottom: 3px solid #0078d4; padding-bottom: 10px; margin: 40px 0 20px 0; letter-spacing: -0.3px;">
9. Indicadores de Huella Operativa
</h2>

<p style="text-align: justify; line-height: 1.8; margin-bottom: 20px; color: #323130; font-size: 13px;">
A los fines de ofrecer una referencia cuantitativa sobre la magnitud operativa del sistema:
</p>

<table style="width: 100%; border-collapse: collapse; margin: 20px 0; font-size: 13px; box-shadow: 0 2px 8px rgba(0,0,0,0.08); border-radius: 8px; overflow: hidden;">
  <thead>
    <tr style="background: linear-gradient(135deg, #0078d4 0%, #106ebe 100%); color: #ffffff;">
      <th style="padding: 14px 18px; text-align: left; font-weight: 600; font-size: 12px; letter-spacing: 0.8px; text-transform: uppercase; border: none; width: 60%;">Indicador</th>
      <th style="padding: 14px 18px; text-align: center; font-weight: 600; font-size: 12px; letter-spacing: 0.8px; text-transform: uppercase; border: none;">Valor</th>
    </tr>
  </thead>
  <tbody>
    <tr style="background: #ffffff;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; color: #323130;">Módulos funcionales del sistema</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; font-weight: 700; color: #0078d4; text-align: center; font-size: 14px;">20</td>
    </tr>
    <tr style="background: #f7f9fc;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; color: #323130;">Módulos operativos diferenciados</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; font-weight: 700; color: #0078d4; text-align: center; font-size: 14px;">28</td>
    </tr>
    <tr style="background: #ffffff;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; color: #323130;">Entidades de información administradas</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; font-weight: 700; color: #0078d4; text-align: center; font-size: 14px;">Más de 90</td>
    </tr>
    <tr style="background: #f7f9fc;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; color: #323130;">Componentes reutilizables de la plataforma</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; font-weight: 700; color: #0078d4; text-align: center; font-size: 14px;">11</td>
    </tr>
    <tr style="background: #ffffff;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; color: #323130;">Procedimientos automatizados de operación</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; font-weight: 700; color: #0078d4; text-align: center; font-size: 14px;">8</td>
    </tr>
    <tr style="background: #f7f9fc;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; color: #323130;">Roles de usuario definidos</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; font-weight: 700; color: #0078d4; text-align: center; font-size: 14px;">13</td>
    </tr>
    <tr style="background: #ffffff;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; color: #323130;">Tipos de reportes automatizados</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; font-weight: 700; color: #0078d4; text-align: center; font-size: 14px;">Más de 15</td>
    </tr>
    <tr style="background: #f7f9fc;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; color: #323130;">Modalidades de pago soportadas</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; font-weight: 700; color: #0078d4; text-align: center; font-size: 14px;">5</td>
    </tr>
    <tr style="background: #ffffff;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; color: #323130;">Estados del ciclo de vida del contrato</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; font-weight: 700; color: #0078d4; text-align: center; font-size: 14px;">7</td>
    </tr>
    <tr style="background: #f7f9fc;">
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; color: #323130;">Estados del ciclo de vida de la cuota</td>
      <td style="padding: 11px 18px; border-top: 1px solid #e1dfdd; vertical-align: middle; font-weight: 700; color: #0078d4; text-align: center; font-size: 14px;">5 (con período de gracia de 7 días)</td>
    </tr>
  </tbody>
</table>

<hr style="border: none; border-top: 2px solid #e1dfdd; margin: 30px 0;">

<!-- ============================================================ -->
<!-- SECCIÓN 10: CONCLUSIÓN                                        -->
<!-- ============================================================ -->
<h2 style="color: #1a3a6e; font-size: 20px; font-weight: 700; border-bottom: 3px solid #0078d4; padding-bottom: 10px; margin: 40px 0 20px 0; letter-spacing: -0.3px;">
10. Conclusión y Valor Entregado
</h2>

<p style="text-align: justify; line-height: 1.8; margin-bottom: 16px; color: #323130; font-size: 13px;">
SISPSA constituye una plataforma de gestión <strong>madura, estable y de nivel productivo</strong>, que integra en una única solución las funciones de gestión comercial, registro clínico, facturación y reportería regulatoria, las cuales tradicionalmente son atendidas por sistemas separados.
</p>

<p style="text-align: justify; line-height: 1.8; margin-bottom: 20px; color: #323130; font-size: 13px;">
El sistema entrega a la organización los siguientes valores operativos y de control:
</p>

<div style="display: table; width: 100%; margin: 20px 0; border-spacing: 0 8px;">

<div style="display: table-row;">
<div style="display: table-cell; padding: 12px 20px 12px 0; vertical-align: middle; width: 140px;">
<span style="display: inline-block; background: #0078d4; color: #ffffff; padding: 6px 14px; border-radius: 14px; font-size: 11px; font-weight: 700; white-space: nowrap; letter-spacing: 0.5px;">EFICIENCIA</span>
</div>
<div style="display: table-cell; padding: 12px 0; vertical-align: middle; color: #323130; font-size: 13px; line-height: 1.7;">
<strong style="color: #1a3a6e;">Eficiencia Operativa:</strong> sustitución de procesos manuales y documentos físicos por flujos de trabajo controlados y trazables.
</div>
</div>

<div style="display: table-row;">
<div style="display: table-cell; padding: 12px 20px 12px 0; vertical-align: middle; width: 140px;">
<span style="display: inline-block; background: #107c10; color: #ffffff; padding: 6px 14px; border-radius: 14px; font-size: 11px; font-weight: 700; white-space: nowrap; letter-spacing: 0.5px;">PRECISIÓN</span>
</div>
<div style="display: table-cell; padding: 12px 0; vertical-align: middle; color: #323130; font-size: 13px; line-height: 1.7;">
<strong style="color: #1a3a6e;">Precisión Financiera:</strong> cálculo automático de comisiones, operación multimoneda y conciliación formal de pagos.
</div>
</div>

<div style="display: table-row;">
<div style="display: table-cell; padding: 12px 20px 12px 0; vertical-align: middle; width: 140px;">
<span style="display: inline-block; background: #e97c00; color: #ffffff; padding: 6px 14px; border-radius: 14px; font-size: 11px; font-weight: 700; white-space: nowrap; letter-spacing: 0.5px;">REGULATORIA</span>
</div>
<div style="display: table-cell; padding: 12px 0; vertical-align: middle; color: #323130; font-size: 13px; line-height: 1.7;">
<strong style="color: #1a3a6e;">Preparación Regulatoria:</strong> generación oportuna de los reportes exigidos por este Despacho, en los formatos establecidos.
</div>
</div>

<div style="display: table-row;">
<div style="display: table-cell; padding: 12px 20px 12px 0; vertical-align: middle; width: 140px;">
<span style="display: inline-block; background: #8764b8; color: #ffffff; padding: 6px 14px; border-radius: 14px; font-size: 11px; font-weight: 700; white-space: nowrap; letter-spacing: 0.5px;">CLÍNICA</span>
</div>
<div style="display: table-cell; padding: 12px 0; vertical-align: middle; color: #323130; font-size: 13px; line-height: 1.7;">
<strong style="color: #1a3a6e;">Gobernanza Clínica:</strong> control efectivo de la cobertura contratada, con prevención del uso indebido de servicios y protección de los intereses financieros de la entidad.
</div>
</div>

<div style="display: table-row;">
<div style="display: table-cell; padding: 12px 20px 12px 0; vertical-align: middle; width: 140px;">
<span style="display: inline-block; background: #c239b3; color: #ffffff; padding: 6px 14px; border-radius: 14px; font-size: 11px; font-weight: 700; white-space: nowrap; letter-spacing: 0.5px;">ESCALABILIDAD</span>
</div>
<div style="display: table-cell; padding: 12px 0; vertical-align: middle; color: #323130; font-size: 13px; line-height: 1.7;">
<strong style="color: #1a3a6e;">Escalabilidad:</strong> arquitectura concebida para soportar la operación multi-clínica, multi-agencia y multi-corporativa de la organización.
</div>
</div>

<div style="display: table-row;">
<div style="display: table-cell; padding: 12px 20px 12px 0; vertical-align: middle; width: 140px;">
<span style="display: inline-block; background: #038387; color: #ffffff; padding: 6px 14px; border-radius: 14px; font-size: 11px; font-weight: 700; white-space: nowrap; letter-spacing: 0.5px;">AUDITABILIDAD</span>
</div>
<div style="display: table-cell; padding: 12px 0; vertical-align: middle; color: #323130; font-size: 13px; line-height: 1.7;">
<strong style="color: #1a3a6e;">Auditabilidad:</strong> trazabilidad completa de las operaciones financieras y clínicas relevantes, con registro íntegro de los eventos críticos.
</div>
</div>

</div>

<p style="text-align: justify; line-height: 1.8; margin-bottom: 16px; color: #323130; font-size: 13px;">
La plataforma se encuentra en condiciones de acompañar el crecimiento de la organización y de mantener el cumplimiento de las exigencias regulatorias y de control interno propias del sector asegurador venezolano.
</p>

<hr style="border: none; border-top: 2px solid #e1dfdd; margin: 40px 0;">

<!-- ============================================================ -->
<!-- BLOQUE DE FIRMA                                               -->
<!-- ============================================================ -->
<h2 style="color: #1a3a6e; font-size: 20px; font-weight: 700; border-bottom: 3px solid #0078d4; padding-bottom: 10px; margin: 40px 0 20px 0; letter-spacing: -0.3px; text-align: center;">
Elaborado por:
</h2>

<div style="text-align: center; padding: 30px 0 20px 0;">

<img src="signature.jpg" alt="Firma del Responsable" style="width: 140px; height: auto; margin: 20px auto 10px auto; display: block;">

<hr style="width: 240px; margin: 15px auto 20px auto; border: none; border-top: 1px solid #605e5c;">

<p style="font-size: 15px; line-height: 1.9; color: #201f1e; margin: 20px 0 30px 0;">
<strong style="font-size: 16px; color: #1a3a6e; letter-spacing: -0.2px;">Rocco Antonio Robortella Guanipa</strong><br/>
<span style="color: #323130; font-weight: 500;">Ingeniero de Sistemas <strong>CIV N-86.440</strong></span><br/>
<span style="color: #605e5c; font-size: 13px;">C.I.: V-9.955.760</span><br/>
<em style="color: #605e5c; font-size: 13px;">Gerencia de Sistemas — SISPSA</em>
</p>
</div>
