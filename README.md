# Bolsa de Trabajo FIS-UNCP

Sistema web para la gestión de ofertas laborales dirigido a estudiantes de la Facultad de Ingeniería de Sistemas de la Universidad Nacional del Centro del Perú.

## 🚀 Configuración Rápida

### 1. Clonar el repositorio
```bash
git clone https://github.com/TU_USUARIO/BolsaFIS3.git
cd BolsaFIS3
```

### 2. Configurar la base de datos
- Ejecuta los scripts SQL en orden desde la carpeta `database/`
- Configura SQL Server y crea la base de datos `BolsaFIS3`

### 3. Configurar la conexión
```bash
# Copia el archivo de configuración
cp includes/config.example.php includes/config.php

# Edita config.php con tus datos de SQL Server
```

### 4. Permisos de carpetas
Asegúrate de que las siguientes carpetas tengan permisos de escritura:
- `uploads/` (fotos, CVs, logos, videos)
- `reports/` (reportes generados)

### 5. Iniciar servidor
```bash
# Si usas XAMPP
# Coloca el proyecto en c:\xampp\htdocs\BolsaFIS3\
# Accede a: http://localhost/BolsaFIS3
```

## 📋 Descripción del Proyecto

Este proyecto es una bolsa de trabajo interna que permite a estudiantes de FIS encontrar oportunidades laborales publicadas por empresas, mientras que los administradores pueden gestionar todo el sistema.

## 🎯 Características Principales

### Para Estudiantes:
- ✅ Registro con validación de edad mínima (16 años)
- ✅ Subida de foto de perfil y CV en PDF
- ✅ Visualización de ofertas laborales
- ✅ Sistema de postulaciones
- ✅ Historial de postulaciones con estados
- ✅ Filtros por modalidad de trabajo

### Para Empresas:
- ✅ Registro con validación de RUC
- ✅ Subida de logo y video de presentación
- ✅ Publicación de ofertas laborales
- ✅ Gestión de postulaciones recibidas
- ✅ Sistema de estados y comentarios para candidatos

### Para Administradores:
- ✅ Dashboard con estadísticas generales
- ✅ Gestión de estudiantes y empresas
- ✅ Rankings de empresas y estudiantes más activos
- ✅ Eliminación lógica de registros

## 🛠️ Tecnologías Utilizadas

### Frontend:
- **HTML5** - Estructura semántica
- **CSS3** - Estilos responsivos y modernos
- **JavaScript** - Validaciones y funcionalidad del cliente

### Backend:
- **PHP 8+** - Lógica del servidor
- **SQL Server** - Base de datos principal
- **SqlSrv Extension** - Conexión PHP-SQL Server

### Base de Datos:
- **Procedimientos Almacenados** - Lógica de negocio
- **Funciones** - Validaciones y cálculos
- **Triggers** - Integridad y auditoría
- **Constraints** - Validaciones a nivel de BD

## 📊 Estructura de la Base de Datos

### Tablas Principales:
- `Estudiantes` - Información de estudiantes
- `Empresas` - Datos de empresas registradas
- `Administradores` - Usuarios administrativos
- `OfertasLaborales` - Ofertas publicadas
- `Postulaciones` - Relación estudiante-oferta
- `AuditoriaOfertas` - Log de actividades

### Procedimientos Almacenados:
- `sp_RegistrarEstudiante` - Registro de estudiantes
- `sp_RegistrarEmpresa` - Registro de empresas
- `sp_CrearOfertaLaboral` - Creación de ofertas
- `sp_PostularOferta` - Sistema de postulaciones

### Funciones:
- `dbo.CalcularEdad()` - Cálculo de edad
- `dbo.ValidarDNI()` - Validación de DNI
- `dbo.ValidarRUC()` - Validación de RUC
- `dbo.GenerarHashContrasena()` - Hash de contraseñas

## 🔧 Configuración e Instalación

### Prerrequisitos:
1. **XAMPP** o servidor web con PHP 8+
2. **SQL Server** 
3. **SQL Server Management Studio**
4. **Extensión SqlSrv** para PHP

### Pasos de Instalación:

#### 1. Configurar la Base de Datos:
```sql
-- Ejecutar en este orden:
1. database/01_create_database.sql
2. database/02_create_tables.sql
3. database/03_create_functions.sql
4. database/04_create_procedures.sql
5. database/05_create_triggers.sql
6. database/06_insert_admin.sql
```

#### 2. Configurar PHP:
```ini
; En php.ini habilitar:
extension=sqlsrv
extension=pdo_sqlsrv
```

#### 3. Configurar Conexión:
Editar `includes/config.php` con tus datos:
```php
define('DB_SERVER', 'ANGEL');
define('DB_USER', 'sa');
define('DB_PASSWORD', 'angelito@10');
define('DB_NAME', 'BolsaTrabajoFIS');
```

#### 4. Configurar Servidor Web:
- Copiar proyecto a htdocs (XAMPP) o directorio web
- Asegurar permisos de escritura en carpeta `uploads/`

## 👥 Credenciales por Defecto

### Administradores:
- **Usuario:** `admin` **Contraseña:** `admin123`
- **Usuario:** `fis_admin` **Contraseña:** `fis2024`

### Estudiantes de Prueba:
- **Correo:** `juan.perez@uncp.edu.pe` **Contraseña:** `juan123`
- **Correo:** `maria.rodriguez@uncp.edu.pe` **Contraseña:** `maria123`
- **Correo:** `carlos.quispe@uncp.edu.pe` **Contraseña:** `carlos123`

### Empresas de Prueba:
- **Correo:** `rrhh@techsolutions.com` **Contraseña:** `tech123`
- **Correo:** `contacto@innovate.com` **Contraseña:** `innova2024`

## 📁 Estructura del Proyecto
```
BolsaFIS3/
├── database/
│   ├── 01_create_database.sql
│   ├── 02_create_tables.sql
│   ├── 03_create_functions.sql
│   ├── 04_create_procedures.sql
│   ├── 05_create_triggers.sql
│   └── 06_insert_admin.sql
├── frontend/
│   ├── css/
│   │   └── styles.css
│   ├── js/
│   │   └── scripts.js
│   ├── uploads/
│   │   ├── fotos/
│   │   ├── cvs/
│   │   ├── logos/
│   │   └── videos/
│   ├── index.html
│   ├── login.html
│   ├── registro_estudiante.html
│   ├── registro_empresa.html
│   ├── dashboard_estudiante.html
│   ├── dashboard_empresa.html
│   └── dashboard_admin.html
└── backend/
    ├── config/
    │   └── database.php
    ├── controllers/
    │   ├── auth.php
    │   ├── estudiante.php
    │   ├── empresa.php
    │   └── admin.php
    └── uploads/
        └── handler.php

## Configuración de Base de Datos
- Servidor: ANGEL
- Usuario: sa
- Contraseña: angelito@10
- Base de datos: BolsaTrabajoFIS

## Tipos de Usuario
1. **Estudiante**: Registro con validaciones de edad, postulación a ofertas
2. **Empresa**: Publicación de ofertas, revisión de candidatos
3. **Administrador**: Gestión general del sistema

## Características Técnicas
- Procedimientos almacenados para operaciones principales
- Funciones para cálculos (edad)
- Triggers para validaciones automáticas
- Constraints para integridad de datos
- Hashing de contraseñas con SHA2_256
