# Bolsa de Trabajo FIS-UNCP

Sistema web para la gestión de ofertas laborales dirigido a estudiantes de la Facultad de Ingeniería de Sistemas de la Universidad Nacional del Centro del Perú.

## 🚀 Configuración Rápida

### 1. Clonar el repositorio
```bash
git clone https://github.com/AngelToDEV-ui/BolsaFIS3.git
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
Copia y edita el archivo de configuración:
```bash
cp includes/config.example.php includes/config.php
```

Luego edita `includes/config.php` con tus datos de SQL Server:
```php
$serverName = "TU_SERVIDOR\\SQLEXPRESS";
$database = "BolsaFIS3";
$username = "tu_usuario";
$password = "tu_contraseña";
```

#### 4. Configurar Servidor Web:
- Copiar proyecto a htdocs (XAMPP) o directorio web
- Asegurar permisos de escritura en carpeta `uploads/`

## 👥 Credenciales por Defecto

### 🔑 Usuario Administrador:
- **Usuario:** `admin` 
- **Contraseña:** `admin123`

### 📋 Usuarios de Prueba:
Los usuarios de prueba se crean automáticamente al ejecutar el script `06_insert_admin.sql`. 
Para seguridad, cambia las contraseñas después de la instalación.

**Nota:** Las credenciales específicas están en los scripts de la base de datos.

## 📁 Estructura del Proyecto
```
BolsaFIS3/
├── admin/                    # Panel administrativo
│   ├── dashboard.php        # Dashboard principal
│   ├── students.php         # Gestión de estudiantes
│   └── companies.php        # Gestión de empresas
├── company/                 # Panel de empresas
│   ├── dashboard.php        # Dashboard empresa
│   ├── profile.php          # Perfil empresa
│   ├── public_profile.php   # Perfil público
│   └── applications.php     # Gestión postulaciones
├── student/                 # Panel de estudiantes
│   ├── dashboard.php        # Dashboard estudiante
│   ├── profile.php          # Perfil estudiante
│   └── applications.php     # Mis postulaciones
├── database/                # Scripts de base de datos
│   ├── 01_create_database.sql
│   ├── 02_create_tables.sql
│   ├── 03_create_functions.sql
│   ├── 04_create_procedures.sql
│   ├── 05_create_triggers.sql
│   └── 06_insert_admin.sql
├── includes/                # Archivos de configuración
│   ├── config.example.php   # Plantilla de configuración
│   └── config.php          # Configuración actual (no incluido)
├── public/                  # Recursos públicos
│   ├── css/
│   │   └── styles.css
│   ├── js/
│   │   └── main.js
│   └── images/
│       ├── logo-fis.svg
│       └── logo-uncp.png
├── reports/                 # Sistema de reportes
│   ├── generate_reports.php
│   └── README.md
├── uploads/                 # Archivos subidos (no incluido)
│   ├── cvs/
│   ├── logos/
│   ├── photos/
│   └── videos/
├── index.html              # Página principal
├── login.php               # Sistema de login
├── register.php            # Registro de usuarios
├── logout.php              # Cerrar sesión
├── check_duplicates.php    # Validación AJAX
└── README.md               # Este archivo
```

## ⚙️ Configuración de Base de Datos

### Configuración requerida:
- **Motor:** SQL Server 2019+ o SQL Express
- **Base de datos:** `BolsaFIS3`
- **Configurar en:** `includes/config.php` (usar plantilla example)

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
