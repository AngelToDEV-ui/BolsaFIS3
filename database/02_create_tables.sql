-- ===================================
-- CREACIÓN DE TABLAS
-- Bolsa de Trabajo FIS-UNCP
-- ===================================

USE BolsaTrabajoFIS;
GO

-- Tabla de Estudiantes
CREATE TABLE Estudiantes (
    id_estudiante INT IDENTITY(1,1) PRIMARY KEY,
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    dni CHAR(8) NOT NULL UNIQUE,
    correo VARCHAR(150) NOT NULL UNIQUE,
    anio_nacimiento INT NOT NULL,
    contrasena_hash VARCHAR(64) NOT NULL, -- SHA2_256 genera 64 caracteres
    foto_perfil VARCHAR(255) NULL,
    cv_archivo VARCHAR(255) NULL,
    fecha_registro DATETIME DEFAULT GETDATE(),
    activo BIT DEFAULT 1,
    
    -- Constraints
    CONSTRAINT CK_Estudiantes_DNI CHECK (dni LIKE '[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]'),
    CONSTRAINT CK_Estudiantes_Correo CHECK (correo LIKE '%@%.%'),
    CONSTRAINT CK_Estudiantes_AnioNacimiento CHECK (anio_nacimiento BETWEEN 1900 AND YEAR(GETDATE())),
    CONSTRAINT CK_Estudiantes_Nombres CHECK (nombres NOT LIKE '%[0-9]%' AND nombres NOT LIKE '%[^a-zA-ZáéíóúÁÉÍÓÚ ]%'),
    CONSTRAINT CK_Estudiantes_Apellidos CHECK (apellidos NOT LIKE '%[0-9]%' AND apellidos NOT LIKE '%[^a-zA-ZáéíóúÁÉÍÓÚ ]%')
);
GO

-- Tabla de Empresas
CREATE TABLE Empresas (
    id_empresa INT IDENTITY(1,1) PRIMARY KEY,
    nombre VARCHAR(200) NOT NULL,
    ruc CHAR(11) NOT NULL UNIQUE,
    correo VARCHAR(150) NOT NULL UNIQUE,
    contrasena_hash VARCHAR(64) NOT NULL,
    descripcion VARCHAR(MAX) NULL,
    logo VARCHAR(255) NULL,
    video_presentacion VARCHAR(255) NULL,
    fecha_registro DATETIME DEFAULT GETDATE(),
    activo BIT DEFAULT 1,
    
    -- Constraints
    CONSTRAINT CK_Empresas_RUC CHECK (ruc LIKE '[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]'),
    CONSTRAINT CK_Empresas_Correo CHECK (correo LIKE '%@%.%')
);
GO

-- Tabla de Administradores
CREATE TABLE Administradores (
    id_admin INT IDENTITY(1,1) PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena_hash VARCHAR(64) NOT NULL,
    nombre_completo VARCHAR(150) NOT NULL,
    correo VARCHAR(150) NOT NULL UNIQUE,
    fecha_creacion DATETIME DEFAULT GETDATE(),
    activo BIT DEFAULT 1,
    
    -- Constraints
    CONSTRAINT CK_Administradores_Correo CHECK (correo LIKE '%@%.%')
);
GO

-- Tabla de Ofertas Laborales
CREATE TABLE OfertasLaborales (
    id_oferta INT IDENTITY(1,1) PRIMARY KEY,
    id_empresa INT NOT NULL,
    titulo VARCHAR(200) NOT NULL,
    descripcion VARCHAR(MAX) NOT NULL,
    requisitos VARCHAR(MAX) NULL,
    salario_min DECIMAL(10,2) NULL,
    salario_max DECIMAL(10,2) NULL,
    modalidad VARCHAR(50) NOT NULL, -- Presencial, Remoto, Híbrido
    ubicacion VARCHAR(200) NULL,
    fecha_publicacion DATETIME DEFAULT GETDATE(),
    fecha_cierre DATETIME NULL,
    activo BIT DEFAULT 1,
    
    -- Foreign Key
    CONSTRAINT FK_OfertasLaborales_Empresa FOREIGN KEY (id_empresa) REFERENCES Empresas(id_empresa),
    
    -- Constraints
    CONSTRAINT CK_OfertasLaborales_Salario CHECK (salario_min IS NULL OR salario_max IS NULL OR salario_min <= salario_max),
    CONSTRAINT CK_OfertasLaborales_Modalidad CHECK (modalidad IN ('Presencial', 'Remoto', 'Híbrido')),
    CONSTRAINT CK_OfertasLaborales_FechaCierre CHECK (fecha_cierre IS NULL OR fecha_cierre > fecha_publicacion)
);
GO

-- Tabla de Postulaciones
CREATE TABLE Postulaciones (
    id_postulacion INT IDENTITY(1,1) PRIMARY KEY,
    id_estudiante INT NOT NULL,
    id_oferta INT NOT NULL,
    fecha_postulacion DATETIME DEFAULT GETDATE(),
    estado VARCHAR(20) DEFAULT 'Pendiente', -- Pendiente, Revisado, Seleccionado, Rechazado
    comentarios VARCHAR(MAX) NULL,
    
    -- Foreign Keys
    CONSTRAINT FK_Postulaciones_Estudiante FOREIGN KEY (id_estudiante) REFERENCES Estudiantes(id_estudiante),
    CONSTRAINT FK_Postulaciones_Oferta FOREIGN KEY (id_oferta) REFERENCES OfertasLaborales(id_oferta),
    
    -- Constraints
    CONSTRAINT CK_Postulaciones_Estado CHECK (estado IN ('Pendiente', 'Revisado', 'Seleccionado', 'Rechazado')),
    
    -- Evitar postulaciones duplicadas
    CONSTRAINT UK_Postulaciones_Estudiante_Oferta UNIQUE (id_estudiante, id_oferta)
);
GO

-- Tabla de Auditoría para Ofertas
CREATE TABLE AuditoriaOfertas (
    id_auditoria INT IDENTITY(1,1) PRIMARY KEY,
    id_oferta INT NOT NULL,
    id_empresa INT NOT NULL,
    accion VARCHAR(20) NOT NULL, -- INSERT, UPDATE, DELETE
    fecha_accion DATETIME DEFAULT GETDATE(),
    usuario_sistema VARCHAR(100) DEFAULT SYSTEM_USER,
    
    -- Foreign Key
    CONSTRAINT FK_AuditoriaOfertas_Oferta FOREIGN KEY (id_oferta) REFERENCES OfertasLaborales(id_oferta)
);
GO

-- Crear índices para mejorar rendimiento
CREATE INDEX IX_Estudiantes_DNI ON Estudiantes(dni);
CREATE INDEX IX_Estudiantes_Correo ON Estudiantes(correo);
CREATE INDEX IX_Empresas_RUC ON Empresas(ruc);
CREATE INDEX IX_Empresas_Correo ON Empresas(correo);
CREATE INDEX IX_OfertasLaborales_Empresa ON OfertasLaborales(id_empresa);
CREATE INDEX IX_OfertasLaborales_FechaPublicacion ON OfertasLaborales(fecha_publicacion);
CREATE INDEX IX_Postulaciones_Estudiante ON Postulaciones(id_estudiante);
CREATE INDEX IX_Postulaciones_Oferta ON Postulaciones(id_oferta);
CREATE INDEX IX_Postulaciones_Estado ON Postulaciones(estado);
GO

PRINT 'Tablas creadas exitosamente.';
GO
