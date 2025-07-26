# Sistema de Reportes - Bolsa de Trabajo FIS-UNCP

## 📊 Generación de Reportes

El sistema ahora puede generar reportes reales en formato HTML que se pueden convertir a PDF.

### 🚀 Características:

- ✅ **Reporte de Estudiantes:** Lista completa con datos personales y estadísticas
- ✅ **Reporte de Empresas:** Lista completa con información corporativa y métricas
- ✅ **Estadísticas Generales:** Resumen del sistema completo
- ✅ **Formato HTML:** Optimizado para impresión y conversión a PDF
- ✅ **Responsive:** Se adapta a diferentes tamaños de papel

### 📁 Ubicación de Reportes:

Los reportes se guardan en: `BolsaFIS3/reports/`

Formato de nombres: `reporte_general_YYYY-MM-DD_HH-MM-SS.html`

### 🖨️ Cómo usar:

1. **Ir al Dashboard Admin**
2. **Hacer clic en "Generar Reportes"**
3. **Esperar a que se genere**
4. **Hacer clic en "Ver/Descargar Reporte PDF"**
5. **Usar Ctrl+P para imprimir o guardar como PDF**

### 💡 Tips:

- **Para PDF:** Usar Ctrl+P y seleccionar "Guardar como PDF"
- **Para Imprimir:** Usar Ctrl+P y seleccionar impresora
- **Configuración:** Asegurarse de incluir gráficos de fondo para mejor apariencia

### 🔧 Mejoras Futuras:

Si quieres generar PDFs directos (sin HTML), puedes instalar TCPDF:

```bash
# En la carpeta BolsaFIS3, ejecutar:
composer install
```

Esto instalará TCPDF para generar PDFs nativos más profesionales.

### 📊 Contenido del Reporte:

**Estadísticas:**
- Total estudiantes activos
- Total empresas registradas  
- Total ofertas laborales
- Total postulaciones

**Estudiantes:**
- Nombres y apellidos
- DNI
- Correo electrónico
- Edad calculada
- Fecha de registro
- Número de postulaciones

**Empresas:**
- Nombre de la empresa
- RUC
- Correo electrónico
- Fecha de registro
- Número de ofertas publicadas
- Número de postulaciones recibidas
