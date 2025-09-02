#!/bin/bash

# Script para instalar automáticamente el cron de cuotas mensuales
# ===============================================================

echo "=== INSTALADOR DE CRON INTELIGENTE PARA CUOTAS ==="
echo ""

# Verificar que estamos en el directorio correcto
if [ ! -f "cron-cuotas.sh" ]; then
    echo "❌ Error: Este script debe ejecutarse desde el directorio del proyecto"
    echo "   Directorio actual: $(pwd)"
    exit 1
fi

# Verificar que el script existe y es ejecutable
if [ ! -x "cron-cuotas.sh" ]; then
    echo "🔧 Haciendo ejecutable el script..."
    chmod +x cron-cuotas.sh
fi

# Obtener la ruta absoluta del proyecto
PROJECT_DIR=$(pwd)
echo "📁 Directorio del proyecto: $PROJECT_DIR"

# Crear la entrada del cron (ejecución diaria a las 6:00 AM)
CRON_ENTRY="0 6 * * * $PROJECT_DIR/cron-cuotas.sh"

echo ""
echo "📅 Configurando cron para ejecutar diariamente a las 6:00 AM"
echo "   Entrada del cron: $CRON_ENTRY"
echo ""

# Verificar si ya existe la entrada del cron
if crontab -l 2>/dev/null | grep -q "cron-cuotas.sh"; then
    echo "⚠️  Ya existe una entrada del cron para cuotas:"
    crontab -l | grep "cron-cuotas.sh"
    echo ""
    read -p "¿Deseas reemplazarla? (s/N): " -n 1 -r
    echo ""
    if [[ ! $REPLY =~ ^[Ss]$ ]]; then
        echo "❌ Instalación cancelada"
        exit 0
    fi
    
    # Remover entrada existente
    crontab -l | grep -v "cron-cuotas.sh" | crontab -
    echo "✅ Entrada anterior removida"
fi

# Agregar nueva entrada al cron
(crontab -l 2>/dev/null; echo "$CRON_ENTRY") | crontab -

if [ $? -eq 0 ]; then
    echo "✅ Cron instalado exitosamente!"
    echo ""
    echo "📋 Entradas del cron actuales:"
    crontab -l
    echo ""
    echo "🔍 Para verificar que funcione:"
    echo "   - Ver logs: tail -f runtime/logs/cron-cuotas.log"
    echo "   - Ver estado: cat runtime/logs/cron-cuotas-status.txt"
    echo "   - Ver cron del sistema: tail -f /var/log/syslog | grep CRON"
    echo ""
    echo "🎯 El script se ejecutará automáticamente:"
    echo "   - DIARIAMENTE: Solo verificación de contratos vencidos por fecha"
    echo "   - MENSUALMENTE (día 1): Flujo completo de cuotas y verificaciones"
else
    echo "❌ Error al instalar el cron"
    exit 1
fi

echo ""
echo "=== INSTALACIÓN COMPLETADA ==="
