#!/bin/bash

# Script inteligente para ejecutar flujo de cuotas automáticas
# Se ejecuta diariamente, pero detecta si es día 1 del mes para ejecución completa

# Configuración
PROJECT_DIR="/home/sispsapp/public_html/v2"
LOG_FILE="runtime/logs/cron-cuotas.log"
DATE=$(date '+%Y-%m-%d %H:%M:%S')

# Función para logging
log_message() {
    echo "[$DATE] $1" | tee -a "$LOG_FILE"
}

write_status() {
    printf "Última ejecución: %s\nEstado: %s\nLog: %s\nTipo: %s\n" \
        "$DATE" "$1" "$LOG_FILE" "$2" > runtime/logs/cron-cuotas-status.txt
}

CRON_FAILED=false

# Cambiar al directorio del proyecto
cd "$PROJECT_DIR" || {
    log_message "ERROR: No se pudo cambiar al directorio del proyecto"
    exit 1
}

mkdir -p runtime/logs || {
    echo "ERROR: No se pudo crear runtime/logs" >&2
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
        write_status "FALLÓ" "Ejecución mensual"
        exit 1
    fi

    ./yii cuota/verificar-tablas >> "$LOG_FILE" 2>&1
    if [ $? -eq 0 ]; then
        log_message "   ✅ Estructura de tablas: OK"
    else
        log_message "   ❌ Estructura de tablas: FALLÓ"
        write_status "FALLÓ" "Ejecución mensual"
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
        CRON_FAILED=true
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
    CRON_FAILED=true
fi


# 4. ACTUALIZACIÓN DE MONTOS (solo en ejecución mensual)
if [ "$EJECUCION_MENSUAL" = true ]; then
    log_message "4. Actualizando montos de cuotas existentes..."
    ./yii cuota/actualizar-montos >> "$LOG_FILE" 2>&1
    if [ $? -eq 0 ]; then
        log_message "   ✅ Actualización de montos: COMPLETADA"
    else
        log_message "   ❌ Actualización de montos: FALLÓ"
        CRON_FAILED=true
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
        CRON_FAILED=true
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
        CRON_FAILED=true
    fi
else
    log_message "6. Saltando verificación completa (ejecución diaria)"
fi

# 7. LIMPIEZA Y MANTENIMIENTO (solo en ejecución mensual)
if [ "$EJECUCION_MENSUAL" = true ]; then
    log_message "7. Limpiando logs antiguos..."
    if find runtime/logs/ -name "cron-cuotas.log*" -mtime +30 -delete; then
        log_message "   ✅ Limpieza de logs: COMPLETADA"
    else
        log_message "   ❌ Limpieza de logs: FALLÓ"
        CRON_FAILED=true
    fi
else
    log_message "7. Saltando limpieza de logs (ejecución diaria)"
fi

# 8. RESUMEN FINAL DE EJECUCIÓN
if [ "$EJECUCION_MENSUAL" = true ]; then
    TIPO_EJECUCION="Ejecución mensual"
    log_message "=== RESUMEN FINAL DE EJECUCIÓN MENSUAL ==="
else
    TIPO_EJECUCION="Verificación diaria"
    log_message "=== RESUMEN DE VERIFICACIÓN DIARIA ==="
fi

log_message "Fecha y hora: $DATE"
if [ "$CRON_FAILED" = true ]; then
    ESTADO_FINAL="FALLÓ"
    log_message "Estado: FALLÓ"
else
    ESTADO_FINAL="COMPLETADO"
    log_message "Estado: COMPLETADO"
fi
log_message "Log guardado en: $LOG_FILE"
log_message "=== FIN DEL FLUJO ==="
write_status "$ESTADO_FINAL" "$TIPO_EJECUCION"

if [ "$CRON_FAILED" = true ]; then
    exit 1
fi

exit 0
