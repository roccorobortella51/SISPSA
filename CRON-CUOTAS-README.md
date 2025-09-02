# 🚀 AUTOMATIZACIÓN INTELIGENTE DE CRON PARA CUOTAS

## 📋 Descripción

Este sistema automatiza la ejecución de cuotas y verificación de contratos vencidos de manera inteligente:
- **DIARIAMENTE**: Solo verificación de contratos vencidos por fecha
- **MENSUALMENTE** (día 1): Flujo completo de cuotas y verificaciones

## 🎯 Funcionalidades Automatizadas

### ✅ **Generación de Cuotas:**
- Cuotas mensuales para todos los contratos activos
- Cuotas atrasadas pendientes
- Actualización de montos de cuotas existentes

### ✅ **Verificación y Suspensión:**
- Contratos vencidos por fecha (`fecha_ven`)
- Contratos con cuotas vencidas (7 días después del vencimiento)
- Suspensión automática de contratos que cumplan las condiciones

### ✅ **Reportes y Monitoreo:**
- Resumen de cuotas atrasadas
- Resumen de contratos próximos a vencer
- Verificación completa del sistema
- Logs detallados de ejecución

## 🛠️ Archivos del Sistema

| Archivo | Descripción |
|---------|-------------|
| `cron-cuotas.sh` | Script inteligente (diario + mensual) |
| `instalar-cron.sh` | Instalador automático del cron |
| `cron-config.txt` | Configuración manual del cron |
| `CRON-CUOTAS-README.md` | Este archivo de instrucciones |

## 🚀 Instalación Automática (Recomendada)

### **Paso 1: Ejecutar el instalador**
```bash
cd /var/www/html/sitios_yii/sipsa
./instalar-cron.sh
```

### **Paso 2: Confirmar la instalación**
El script te mostrará:
- ✅ Cron instalado exitosamente
- 📋 Entradas del cron actuales
- 🔍 Comandos para verificar el funcionamiento
- 🎯 Comportamiento inteligente configurado

## 🔧 Instalación Manual

### **Paso 1: Editar el crontab**
```bash
crontab -e
```

### **Paso 2: Agregar la línea del cron**
```bash
# Ejecutar diariamente a las 6:00 AM (comportamiento inteligente)
0 6 * * * /var/www/html/sitios_yii/sipsa/cron-cuotas.sh
```

### **Paso 3: Verificar la instalación**
```bash
crontab -l
```

## 📅 Formato del Cron

```
┌───────────── minuto (0-59)
│ ┌─────────── hora (0-23)
│ │ ┌───────── día del mes (1-31)
│ │ │ ┌─────── mes (1-12)
│ │ │ │ ┌───── día de la semana (0-7, 0 y 7 = domingo)
│ │ │ │ │
0 6 * * * /ruta/al/script.sh
```

## 🧠 Comportamiento Inteligente

El script `cron-cuotas.sh` detecta automáticamente el tipo de ejecución:

### **🔍 Ejecución Diaria (días 2-31 del mes):**
- ✅ Verificación de contratos vencidos por fecha (`fecha_ven`)
- ✅ Verificación de contratos por cuotas vencidas (7 días)
- ✅ Suspensión automática de contratos
- ⏭️ **NO** genera cuotas nuevas
- ⏭️ **NO** ejecuta verificaciones completas

### **🚀 Ejecución Mensual (día 1 del mes):**
- ✅ **TODAS** las funcionalidades diarias
- ✅ Verificaciones previas (BD, tablas)
- ✅ Generación completa de cuotas
- ✅ Actualización de montos
- ✅ Resúmenes y reportes
- ✅ Verificación completa del sistema
- ✅ Limpieza y mantenimiento

**Explicación:** `0 6 * * *` significa:
- **0** = minuto 0
- **6** = hora 6 (6:00 AM)
- **\*** = todos los días del mes
- **\*** = todos los meses
- **\*** = cualquier día de la semana

## 🔍 Verificación del Funcionamiento

### **1. Verificar que el cron esté instalado:**
```bash
crontab -l
```

### **2. Ver logs del sistema:**
```bash
tail -f /var/log/syslog | grep CRON
```

### **3. Ver logs de ejecución:**
```bash
tail -f runtime/logs/cron-cuotas-mensual.log
```

### **4. Ver estado de la última ejecución:**
```bash
cat runtime/logs/cron-cuotas-mensual-status.txt
```

## ⚠️ Solución de Problemas

### **Problema: El cron no se ejecuta**
**Solución:**
1. Verificar permisos del script:
   ```bash
   ls -la cron-cuotas-mensual.sh
   ```

2. Verificar que el usuario tenga permisos:
   ```bash
   sudo chown $USER:$USER cron-cuotas-mensual.sh
   chmod +x cron-cuotas-mensual.sh
   ```

3. Verificar logs del sistema:
   ```bash
   tail -f /var/log/syslog | grep CRON
   ```

### **Problema: Error de permisos**
**Solución:**
```bash
sudo chmod +x cron-cuotas-mensual.sh
sudo chown $USER:$USER cron-cuotas-mensual.sh
```

### **Problema: Ruta incorrecta**
**Solución:**
1. Verificar la ruta absoluta:
   ```bash
   pwd
   ```

2. Actualizar el cron con la ruta correcta:
   ```bash
   crontab -e
   # Cambiar la ruta en la línea del cron
   ```

## 📊 Monitoreo y Mantenimiento

### **Logs del Sistema:**
- **Log principal:** `runtime/logs/cron-cuotas-mensual.log`
- **Estado de ejecución:** `runtime/logs/cron-cuotas-mensual-status.txt`
- **Logs del sistema:** `/var/log/syslog`

### **Limpieza Automática:**
El script limpia automáticamente logs antiguos (más de 90 días).

### **Verificación Manual:**
```bash
# Ejecutar manualmente para pruebas
./cron-cuotas.sh

# Verificar comandos individuales
./yii cuota/verificar-vencidas
./yii cuota/generar-mensual
```

## 🎯 Comandos Útiles

### **Gestión del Cron:**
```bash
crontab -e          # Editar cron
crontab -l          # Listar cron
crontab -r          # Remover todo el cron
```

### **Verificación del Sistema:**
```bash
./yii cuota/verificar-todo          # Verificación completa
./yii cuota/verificar-conexion      # Verificar BD
./yii cuota/verificar-contratos     # Verificar contratos
```

### **Reportes:**
```bash
./yii cuota/resumen-atrasadas       # Cuotas atrasadas
./yii cuota/resumen-proximos-vencer # Contratos próximos a vencer
```

## 🔒 Seguridad

- ✅ El script verifica que sea el primer día del mes
- ✅ Logs detallados de todas las operaciones
- ✅ Manejo de errores y validaciones
- ✅ Limpieza automática de logs antiguos

## 📞 Soporte

Si tienes problemas con la automatización:

1. **Verificar logs:** `tail -f runtime/logs/cron-cuotas-mensual.log`
2. **Verificar cron:** `crontab -l`
3. **Ejecutar manualmente:** `./cron-cuotas-mensual.sh`
4. **Verificar permisos:** `ls -la cron-cuotas-mensual.sh`

---

**🎉 ¡Tu sistema de cuotas automáticas está listo para funcionar!**
