#!/bin/bash

# Script para ejecutar flujo completo de cuotas automáticas
# Debe ejecutarse diariamente con cron

# Configuración
PROJECT_DIR="/home/rescorihuela/html/sitios_yii/sipsa"
LOG_FILE="runtime/logs/cron-cuotas.log"
DATE=$(date '+%Y-%m-%d %H:%M:%S')

# Función para logging
log_message() {
    echo "[$DATE] $1" | tee -a "$LOG_FILE"
}

# Cambiar al directorio del proyecto
cd "$PROJECT_DIR" || {
    log_message "ERROR: No se pudo cambiar al directorio del proyecto"
    exit 1
}

log_message "=== INICIANDO FLUJO COMPLETO DE CUOTAS ==="

# 1. VERIFICACIONES PREVIAS
log_message "1. Ejecutando verificaciones previas..."
./yii cuota/verificar-conexion >> "$LOG_FILE" 2>&1
if [ $? -eq 0 ]; then
    log_message "   ✅ Conexión a base de datos: OK"
else
    log_message "   ❌ Conexión a base de datos: FALLÓ"
    exit 1
fi

./yii cuota/verificar-tablas >> "$LOG_FILE" 2>&1
if [ $? -eq 0 ]; then
    log_message "   ✅ Estructura de tablas: OK"
else
    log_message "   ❌ Estructura de tablas: FALLÓ"
    exit 1
fi

# 2. GENERACIÓN DE CUOTAS ATRASADAS
log_message "2. Generando cuotas atrasadas..."
./yii cuota/generar-atrasadas >> "$LOG_FILE" 2>&1
if [ $? -eq 0 ]; then
    log_message "   ✅ Generación de cuotas atrasadas: COMPLETADA"
else
    log_message "   ❌ Generación de cuotas atrasadas: FALLÓ"
fi

# 3. GENERACIÓN DE CUOTAS MENSUALES (solo el día 1)
if [ "$(date '+%d')" = "01" ]; then
    log_message "3. Generando cuotas mensuales (es día 1 del mes)..."
    ./yii cuota/generar-mensual >> "$LOG_FILE" 2>&1
    if [ $? -eq 0 ]; then
        log_message "   ✅ Generación de cuotas mensuales: COMPLETADA"
    else
        log_message "   ❌ Generación de cuotas mensuales: FALLÓ"
    fi
else
    log_message "3. Saltando generación mensual (no es día 1 del mes)"
fi

# 4. VERIFICACIÓN DE CUOTAS VENCIDAS Y SUSPENSIÓN
log_message "4. Verificando cuotas vencidas y suspendiendo contratos..."
./yii cuota/verificar-vencidas >> "$LOG_FILE" 2>&1
if [ $? -eq 0 ]; then
    log_message "   ✅ Verificación de cuotas vencidas: COMPLETADA"
else
    log_message "   ❌ Verificación de cuotas vencidas: FALLÓ"
fi

# 5. GENERACIÓN COMPLETA (cuotas atrasadas + cuotas del mes actual)
log_message "5. Ejecutando generación completa de cuotas..."
./yii cuota/generar >> "$LOG_FILE" 2>&1
if [ $? -eq 0 ]; then
    log_message "   ✅ Generación completa: COMPLETADA"
else
    log_message "   ❌ Generación completa: FALLÓ"
fi

# 6. ACTUALIZACIÓN DE MONTOS (si es necesario)
log_message "6. Actualizando montos de cuotas existentes..."
./yii cuota/actualizar-montos >> "$LOG_FILE" 2>&1
if [ $? -eq 0 ]; then
    log_message "   ✅ Actualización de montos: COMPLETADA"
else
    log_message "   ❌ Actualización de montos: FALLÓ"
fi

# 7. RESUMEN FINAL
log_message "7. Generando resumen final..."
./yii cuota/resumen-atrasadas >> "$LOG_FILE" 2>&1
if [ $? -eq 0 ]; then
    log_message "   ✅ Resumen generado: COMPLETADO"
else
    log_message "   ❌ Resumen: FALLÓ"
fi

# 8. VERIFICACIÓN COMPLETA
log_message "8. Ejecutando verificación completa del sistema..."
./yii cuota/verificar-todo >> "$LOG_FILE" 2>&1
if [ $? -eq 0 ]; then
    log_message "   ✅ Verificación completa: TODAS LAS VERIFICACIONES PASARON"
else
    log_message "   ⚠️  Verificación completa: SE ENCONTRARON PROBLEMAS"
fi

# 9. LIMPIEZA Y MANTENIMIENTO
log_message "9. Limpiando logs antiguos..."
find runtime/logs/ -name "cron-cuotas.log*" -mtime +30 -delete
log_message "   ✅ Limpieza de logs: COMPLETADA"

# 10. RESUMEN DE EJECUCIÓN
log_message "=== RESUMEN DE EJECUCIÓN ==="
log_message "Fecha y hora: $DATE"
log_message "Estado: COMPLETADO"
log_message "Log guardado en: $LOG_FILE"
log_message "=== FIN DEL FLUJO ===\n"

# Crear archivo de estado para monitoreo
echo "Última ejecución: $DATE" > runtime/logs/cron-cuotas-status.txt
echo "Estado: COMPLETADO" >> runtime/logs/cron-cuotas-status.txt
echo "Log: $LOG_FILE" >> runtime/logs/cron-cuotas-status.txt
