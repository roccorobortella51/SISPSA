#!/bin/bash

# Script inteligente para ejecutar flujo de cuotas automáticas
# Se ejecuta diariamente, pero detecta si es día 1 del mes para ejecución completa

# Configuración
PROJECT_DIR="/var/www/html/sitios_yii/sipsa"
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

# Detectar si es ejecución mensual (día 1) o diaria
ES_DIA_UNO=$(date '+%d')
if [ "$ES_DIA_UNO" = "01" ]; then
    log_message "=== INICIANDO FLUJO MENSUAL COMPLETO DE CUOTAS ==="
    EJECUCION_MENSUAL=true
else
    log_message "=== INICIANDO VERIFICACIÓN DIARIA DE CONTRATOS VENCIDOS ==="
    EJECUCION_MENSUAL=false
fi

# 1. VERIFICACIONES PREVIAS (solo en ejecución mensual)
if [ "$EJECUCION_MENSUAL" = true ]; then
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
else
    log_message "1. Saltando verificaciones previas (ejecución diaria)"
fi

# 2. GENERACIÓN COMPLETA (solo en ejecución mensual)
if [ "$EJECUCION_MENSUAL" = true ]; then
    log_message "2. Ejecutando generación completa de cuotas..."
    ./yii cuota/generar >> "$LOG_FILE" 2>&1
    if [ $? -eq 0 ]; then
        log_message "   ✅ Generación completa: COMPLETADA"
    else
        log_message "   ❌ Generación completa: FALLÓ"
    fi
else
    log_message "2. Saltando generación de cuotas (ejecución diaria)"
fi

# 3. VERIFICACIÓN DE CONTRATOS VENCIDOS Y SUSPENSIÓN (SIEMPRE)
log_message "3. Verificando contratos vencidos y suspendiendo..."
./yii cuota/verificar-vencidas >> "$LOG_FILE" 2>&1
if [ $? -eq 0 ]; then
    log_message "   ✅ Verificación de contratos vencidos: COMPLETADA"
else
    log_message "   ❌ Verificación de contratos vencidos: FALLÓ"
fi


# 4. ACTUALIZACIÓN DE MONTOS (solo en ejecución mensual)
if [ "$EJECUCION_MENSUAL" = true ]; then
    log_message "4. Actualizando montos de cuotas existentes..."
    ./yii cuota/actualizar-montos >> "$LOG_FILE" 2>&1
    if [ $? -eq 0 ]; then
        log_message "   ✅ Actualización de montos: COMPLETADA"
    else
        log_message "   ❌ Actualización de montos: FALLÓ"
    fi
else
    log_message "4. Saltando actualización de montos (ejecución diaria)"
fi

# 5. RESUMEN FINAL (solo en ejecución mensual)
if [ "$EJECUCION_MENSUAL" = true ]; then
    log_message "5. Generando resumen final..."
    ./yii cuota/resumen-atrasadas >> "$LOG_FILE" 2>&1
    if [ $? -eq 0 ]; then
        log_message "   ✅ Resumen generado: COMPLETADO"
    else
        log_message "   ❌ Resumen: FALLÓ"
    fi
else
    log_message "5. Saltando resumen (ejecución diaria)"
fi

# 6. VERIFICACIÓN COMPLETA (solo en ejecución mensual)
if [ "$EJECUCION_MENSUAL" = true ]; then
    log_message "6. Ejecutando verificación completa del sistema..."
    ./yii cuota/verificar-todo >> "$LOG_FILE" 2>&1
    if [ $? -eq 0 ]; then
        log_message "   ✅ Verificación completa: TODAS LAS VERIFICACIONES PASARON"
    else
        log_message "   ⚠️  Verificación completa: SE ENCONTRARON PROBLEMAS"
    fi
else
    log_message "6. Saltando verificación completa (ejecución diaria)"
fi

# 7. LIMPIEZA Y MANTENIMIENTO (solo en ejecución mensual)
if [ "$EJECUCION_MENSUAL" = true ]; then
    log_message "7. Limpiando logs antiguos..."
    find runtime/logs/ -name "cron-cuotas.log*" -mtime +30 -delete
    log_message "   ✅ Limpieza de logs: COMPLETADA"
else
    log_message "7. Saltando limpieza de logs (ejecución diaria)"
fi

# 8. RESUMEN FINAL DE EJECUCIÓN
if [ "$EJECUCION_MENSUAL" = true ]; then
    log_message "=== RESUMEN FINAL DE EJECUCIÓN MENSUAL ==="
    log_message "Fecha y hora: $DATE"
    log_message "Estado: COMPLETADO"
    log_message "Log guardado en: $LOG_FILE"
    log_message "=== FIN DEL FLUJO MENSUAL ===\n"
    
    # Crear archivo de estado para monitoreo mensual
    echo "Última ejecución mensual: $DATE" > runtime/logs/cron-cuotas-status.txt
    echo "Estado: COMPLETADO" >> runtime/logs/cron-cuotas-status.txt
    echo "Log: $LOG_FILE" >> runtime/logs/cron-cuotas-status.txt
    echo "Tipo: Ejecución mensual" >> runtime/logs/cron-cuotas-status.txt
else
    log_message "=== RESUMEN DE VERIFICACIÓN DIARIA ==="
    log_message "Fecha y hora: $DATE"
    log_message "Estado: COMPLETADO"
    log_message "Log guardado en: $LOG_FILE"
    log_message "=== FIN DE VERIFICACIÓN DIARIA ===\n"
    
    # Crear archivo de estado para monitoreo diario
    echo "Última ejecución diaria: $DATE" > runtime/logs/cron-cuotas-status.txt
    echo "Estado: COMPLETADO" >> runtime/logs/cron-cuotas-status.txt
    echo "Log: $LOG_FILE" >> runtime/logs/cron-cuotas-status.txt
    echo "Tipo: Verificación diaria" >> runtime/logs/cron-cuotas-status.txt
fi
