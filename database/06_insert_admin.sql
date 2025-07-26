-- ===================================
-- INSERCIÓN DE DATOS INICIALES
-- Bolsa de Trabajo FIS-UNCP
-- ===================================

USE BolsaTrabajoFIS;
GO

-- ====================================
-- INSERTAR ADMINISTRADOR POR DEFECTO
-- ====================================

-- Insertar administrador por defecto
-- Usuario: admin, Contraseña: admin123
INSERT INTO Administradores (usuario, contrasena_hash, nombre_completo, correo)
VALUES ('admin', dbo.GenerarHashContrasena('admin123'), 'Administrador Sistema', 'admin@fis.uncp.edu.pe');

-- Insertar administrador adicional
-- Usuario: fis_admin, Contraseña: fis2024
INSERT INTO Administradores (usuario, contrasena_hash, nombre_completo, correo)
VALUES ('fis_admin', dbo.GenerarHashContrasena('fis2024'), 'Administrador FIS', 'administrador@fis.uncp.edu.pe');

GO

-- ====================================
-- INSERTAR DATOS DE PRUEBA
-- ====================================

-- Insertar empresas de ejemplo
EXEC sp_RegistrarEmpresa 
    @nombre = 'Tech Solutions SAC',
    @ruc = '20123456789',
    @correo = 'rrhh@techsolutions.com',
    @contrasena = 'tech123',
    @descripcion = 'Empresa líder en desarrollo de software y soluciones tecnológicas.',
    @logo = NULL,
    @video_presentacion = NULL;

EXEC sp_RegistrarEmpresa 
    @nombre = 'Innovate Corp',
    @ruc = '20987654321',
    @correo = 'contacto@innovate.com',
    @contrasena = 'innova2024',
    @descripcion = 'Especialistas en transformación digital y consultoría tecnológica.',
    @logo = NULL,
    @video_presentacion = NULL;

-- Insertar estudiantes de ejemplo
EXEC sp_RegistrarEstudiante 
    @nombres = 'Juan Carlos',
    @apellidos = 'Pérez García',
    @dni = '12345678',
    @correo = 'juan.perez@uncp.edu.pe',
    @anio_nacimiento = 2000,
    @contrasena = 'juan123',
    @foto_perfil = NULL,
    @cv_archivo = NULL;

EXEC sp_RegistrarEstudiante 
    @nombres = 'María Elena',
    @apellidos = 'Rodríguez López',
    @dni = '87654321',
    @correo = 'maria.rodriguez@uncp.edu.pe',
    @anio_nacimiento = 1999,
    @contrasena = 'maria123',
    @foto_perfil = NULL,
    @cv_archivo = NULL;

EXEC sp_RegistrarEstudiante 
    @nombres = 'Carlos Alberto',
    @apellidos = 'Quispe Huamán',
    @dni = '11223344',
    @correo = 'carlos.quispe@uncp.edu.pe',
    @anio_nacimiento = 2001,
    @contrasena = 'carlos123',
    @foto_perfil = NULL,
    @cv_archivo = NULL;

-- Insertar ofertas laborales de ejemplo
DECLARE @fecha_cierre1 DATETIME, @fecha_cierre2 DATETIME, @fecha_cierre3 DATETIME;
SET @fecha_cierre1 = DATEADD(DAY, 30, GETDATE());
SET @fecha_cierre2 = DATEADD(DAY, 45, GETDATE());
SET @fecha_cierre3 = DATEADD(DAY, 60, GETDATE());

EXEC sp_CrearOfertaLaboral 
    @id_empresa = 1,
    @titulo = 'Desarrollador Full Stack Junior',
    @descripcion = 'Buscamos desarrollador junior con conocimientos en HTML, CSS, JavaScript y SQL Server. Excelente oportunidad para recién egresados.',
    @requisitos = 'Conocimientos básicos en programación web, bases de datos, trabajo en equipo.',
    @salario_min = 1500.00,
    @salario_max = 2500.00,
    @modalidad = 'Híbrido',
    @ubicacion = 'Huancayo, Junín',
    @fecha_cierre = @fecha_cierre1;

EXEC sp_CrearOfertaLaboral 
    @id_empresa = 1,
    @titulo = 'Analista de Sistemas',
    @descripcion = 'Posición para analista de sistemas con experiencia en análisis de requerimientos y documentación técnica.',
    @requisitos = 'Titulado en Ingeniería de Sistemas, experiencia mínima 1 año, conocimientos en UML.',
    @salario_min = 2000.00,
    @salario_max = 3000.00,
    @modalidad = 'Presencial',
    @ubicacion = 'Huancayo, Junín',
    @fecha_cierre = @fecha_cierre2;

EXEC sp_CrearOfertaLaboral 
    @id_empresa = 2,
    @titulo = 'Consultor TI Junior',
    @descripcion = 'Oportunidad para jóvenes profesionales interesados en consultoría tecnológica y transformación digital.',
    @requisitos = 'Recién egresado, buenas habilidades de comunicación, disposición para viajar.',
    @salario_min = 1800.00,
    @salario_max = 2800.00,
    @modalidad = 'Remoto',
    @ubicacion = 'Lima, Perú',
    @fecha_cierre = @fecha_cierre3;

-- Insertar algunas postulaciones de ejemplo
EXEC sp_PostularOferta @id_estudiante = 1, @id_oferta = 1;
EXEC sp_PostularOferta @id_estudiante = 1, @id_oferta = 3;
EXEC sp_PostularOferta @id_estudiante = 2, @id_oferta = 1;
EXEC sp_PostularOferta @id_estudiante = 2, @id_oferta = 2;
EXEC sp_PostularOferta @id_estudiante = 3, @id_oferta = 1;
EXEC sp_PostularOferta @id_estudiante = 3, @id_oferta = 3;

GO

-- ====================================
-- VERIFICAR DATOS INSERTADOS
-- ====================================

-- Mostrar resumen de datos insertados
SELECT 'RESUMEN DE DATOS INSERTADOS' as titulo;

SELECT 'Administradores' as tabla, COUNT(*) as total FROM Administradores
UNION ALL
SELECT 'Empresas' as tabla, COUNT(*) as total FROM Empresas
UNION ALL
SELECT 'Estudiantes' as tabla, COUNT(*) as total FROM Estudiantes
UNION ALL
SELECT 'Ofertas Laborales' as tabla, COUNT(*) as total FROM OfertasLaborales
UNION ALL
SELECT 'Postulaciones' as tabla, COUNT(*) as total FROM Postulaciones;

-- Mostrar información de login de administradores
SELECT 
    'CREDENCIALES DE ADMINISTRADORES' as info,
    usuario, 
    nombre_completo, 
    correo,
    'Contraseña en README.md' as password_info
FROM Administradores;

-- Mostrar información de estudiantes de prueba
SELECT 
    'ESTUDIANTES DE PRUEBA' as info,
    nombres + ' ' + apellidos as nombre_completo,
    dni,
    correo,
    dbo.CalcularEdad(anio_nacimiento) as edad,
    'Contraseña: [nombre]123' as password_info
FROM Estudiantes;

-- Mostrar información de empresas de prueba
SELECT 
    'EMPRESAS DE PRUEBA' as info,
    nombre,
    ruc,
    correo,
    'Contraseña en script' as password_info
FROM Empresas;

GO

PRINT '====================================';
PRINT 'DATOS INICIALES INSERTADOS EXITOSAMENTE';
PRINT '====================================';
PRINT 'Credenciales de Administrador:';
PRINT 'Usuario: admin, Contraseña: admin123';
PRINT 'Usuario: fis_admin, Contraseña: fis2024';
PRINT '====================================';
PRINT 'Estudiantes de prueba:';
PRINT 'juan.perez@uncp.edu.pe - Contraseña: juan123';
PRINT 'maria.rodriguez@uncp.edu.pe - Contraseña: maria123';
PRINT 'carlos.quispe@uncp.edu.pe - Contraseña: carlos123';
PRINT '====================================';
PRINT 'Empresas de prueba:';
PRINT 'rrhh@techsolutions.com - Contraseña: tech123';
PRINT 'contacto@innovate.com - Contraseña: innova2024';
PRINT '====================================';
GO
