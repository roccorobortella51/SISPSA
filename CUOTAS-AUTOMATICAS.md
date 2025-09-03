# Sistema de Cuotas Automáticas

## Descripción
Sistema automatizado para la gestión de cuotas de suscripción que incluye:
- Generación automática de cuotas iniciales al crear contratos
- Generación mensual de cuotas el día 1 de cada mes
- Verificación diaria de cuotas vencidas
- Suspensión automática de contratos con cuotas impagas

## Flujo de Negocio

### 1. Creación de Contrato
- **Al crear un contrato:** Se genera automáticamente la cuota inicial
- **Fecha de vencimiento:** Primer día del mes de inicio del contrato
- **Estado:** Pendiente

### 2. Generación Mensual de Cuotas
- **Frecuencia:** El día 1 de cada mes
- **Proceso:** Se genera una cuota para todos los contratos activos
- **Fecha de vencimiento:** Primer día del mes actual

### 3. Período de Pago
- **Duración:** Primeros 5 días del mes
- **Vencimiento:** Al finalizar el día 5
- **Consecuencia:** Si no se paga, el contrato se suspende automáticamente

## Comandos Disponibles

### Generación de Cuotas
```bash
# Generar cuotas del mes actual y atrasadas
./yii cuota/generar

# Generar solo cuotas atrasadas
./yii cuota/generar-atrasadas

# Generar cuotas mensuales (solo el día 1)
./yii cuota/generar-mensual

# Generar cuota inicial para un contrato específico
./yii cuota/generar-inicial [contrato_id]
```

### Verificación y Monitoreo
```bash
# Verificar cuotas vencidas y suspender contratos
./yii cuota/verificar-vencidas

# Resumen de cuotas atrasadas
./yii cuota/resumen-atrasadas

# Verificación completa diaria
./yii cuota/verificar-diario

# Verificar estado de contratos
./yii cuota/verificar-estado-contratos
```

### Verificaciones Previas
```bash
# Ejecutar todas las verificaciones
./yii cuota/verificar-todo

# Verificaciones individuales
./yii cuota/verificar-conexion
./yii cuota/verificar-tablas
./yii cuota/verificar-contratos
./yii cuota/verificar-tasa
```

### Mantenimiento
```bash
# Actualizar montos de cuotas existentes
./yii cuota/actualizar-montos

# Corregir cuotas futuras incorrectas
./yii cuota/corregir-cuotas-futuras
```

## Configuración del Cron Job

### 1. Crear entrada en crontab
```bash
# Editar crontab
crontab -e

# Agregar esta línea para ejecutar diariamente a las 6:00 AM
0 6 * * * /home/rescorihuela/html/sitios_yii/sipsa/cron-cuotas.sh
```

### 2. Verificar que el script sea ejecutable
```bash
chmod +x /home/rescorihuela/html/sitios_yii/sipsa/cron-cuotas.sh
```

### 3. Probar el script manualmente
```bash
./cron-cuotas.sh
```

## Estructura de la Base de Datos

### Tabla `contratos`
- `id`: ID del contrato
- `fecha_ini`: Fecha de inicio del contrato
- `monto`: Monto de la cuota mensual
- `estatus`: Estado del contrato (activo, Creado, Registrado, suspendido)

### Tabla `cuotas`
- `id`: ID de la cuota
- `contrato_id`: ID del contrato asociado
- `fecha_vencimiento`: Fecha de vencimiento (primer día del mes)
- `monto_usd`: Monto de la cuota en USD
- `Estatus`: Estado de la cuota (pendiente, pagado)
- `rate_usd_bs`: Tasa de cambio USD/BS

### Tabla `tasa_cambio`
- `fecha`: Fecha de la tasa
- `hora`: Hora de la tasa
- `tasa_cambio`: Valor de la tasa

## Logs y Monitoreo

### Archivo de Log
- **Ubicación:** `runtime/logs/cron-cuotas.log`
- **Rotación:** Automática (se mantienen solo los últimos 30 días)

### Contenido del Log
- Fecha y hora de ejecución
- Cuotas generadas
- Contratos suspendidos
- Errores encontrados

## Solución de Problemas

### Cuotas no se generan
1. Verificar conexión a la base de datos: `./yii cuota/verificar-conexion`
2. Verificar que existan contratos activos: `./yii cuota/verificar-contratos`
3. Verificar que las tablas existan: `./yii cuota/verificar-tablas`

### Contratos no se suspenden
1. Verificar que las cuotas tengan fechas de vencimiento correctas
2. Ejecutar verificación manual: `./yii cuota/verificar-vencidas`
3. Revisar logs en `runtime/logs/cron-cuotas.log`

### Montos incorrectos
1. Verificar montos en contratos: `./yii cuota/verificar-estado-contratos`
2. Actualizar montos de cuotas: `./yii cuota/actualizar-montos`
3. Regenerar cuotas si es necesario: `./yii cuota/generar`

## Notas Importantes

- **El cron job debe ejecutarse diariamente** para verificar cuotas vencidas
- **La generación mensual solo funciona el día 1** de cada mes
- **Los contratos se suspenden automáticamente** después de 5 días sin pago
- **Las cuotas iniciales se generan automáticamente** al crear contratos
- **Siempre verificar la configuración** antes de ejecutar comandos críticos
