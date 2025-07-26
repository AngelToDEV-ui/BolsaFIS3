-- ===================================
-- CREACIÓN DE PROCEDIMIENTOS ALMACENADOS
-- Bolsa de Trabajo FIS-UNCP
-- ===================================

USE BolsaTrabajoFIS;
GO

-- ====================================
-- PROCEDIMIENTOS PARA ESTUDIANTES
-- ====================================

-- Procedimiento para registrar estudiante
CREATE PROCEDURE sp_RegistrarEstudiante
    @nombres VARCHAR(100),
    @apellidos VARCHAR(100),
    @dni CHAR(8),
    @correo VARCHAR(150),
    @anio_nacimiento INT,
    @contrasena VARCHAR(255),
    @foto_perfil VARCHAR(255) = NULL,
    @cv_archivo VARCHAR(255) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    
    BEGIN TRY
        -- Validar edad mínima
        IF dbo.ValidarEdadMinima(@anio_nacimiento) = 0
        BEGIN
            RAISERROR('La edad mínima permitida es 16 años.', 16, 1);
            RETURN;
        END
        
        -- Validar DNI
        IF dbo.ValidarDNI(@dni) = 0
        BEGIN
            RAISERROR('El DNI debe tener 8 dígitos válidos.', 16, 1);
            RETURN;
        END
        
        -- Generar hash de contraseña
        DECLARE @hash_contrasena VARCHAR(64);
        SET @hash_contrasena = dbo.GenerarHashContrasena(@contrasena);
        
        -- Insertar estudiante
        INSERT INTO Estudiantes (nombres, apellidos, dni, correo, anio_nacimiento, contrasena_hash, foto_perfil, cv_archivo)
        VALUES (@nombres, @apellidos, @dni, @correo, @anio_nacimiento, @hash_contrasena, @foto_perfil, @cv_archivo);
        
        SELECT SCOPE_IDENTITY() as id_estudiante, 'Estudiante registrado exitosamente.' as mensaje;
        
    END TRY
    BEGIN CATCH
        SELECT ERROR_MESSAGE() as error;
    END CATCH
END
GO

-- Procedimiento para login de estudiante
CREATE PROCEDURE sp_LoginEstudiante
    @correo VARCHAR(150),
    @contrasena VARCHAR(255)
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @id_estudiante INT, @hash_almacenado VARCHAR(64);
    
    SELECT @id_estudiante = id_estudiante, @hash_almacenado = contrasena_hash
    FROM Estudiantes
    WHERE correo = @correo AND activo = 1;
    
    IF @id_estudiante IS NULL
    BEGIN
        SELECT 0 as exito, 'Correo no encontrado o cuenta inactiva.' as mensaje;
        RETURN;
    END
    
    IF dbo.ValidarContrasena(@contrasena, @hash_almacenado) = 1
    BEGIN
        SELECT 1 as exito, 'Login exitoso.' as mensaje, @id_estudiante as id_estudiante;
    END
    ELSE
    BEGIN
        SELECT 0 as exito, 'Contraseña incorrecta.' as mensaje;
    END
END
GO

-- Procedimiento para actualizar perfil de estudiante
CREATE PROCEDURE sp_ActualizarPerfilEstudiante
    @id_estudiante INT,
    @nombres VARCHAR(100),
    @apellidos VARCHAR(100),
    @correo VARCHAR(150),
    @foto_perfil VARCHAR(255) = NULL,
    @cv_archivo VARCHAR(255) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    
    BEGIN TRY
        UPDATE Estudiantes
        SET nombres = @nombres,
            apellidos = @apellidos,
            correo = @correo,
            foto_perfil = ISNULL(@foto_perfil, foto_perfil),
            cv_archivo = ISNULL(@cv_archivo, cv_archivo)
        WHERE id_estudiante = @id_estudiante;
        
        SELECT 'Perfil actualizado exitosamente.' as mensaje;
        
    END TRY
    BEGIN CATCH
        SELECT ERROR_MESSAGE() as error;
    END CATCH
END
GO

-- ====================================
-- PROCEDIMIENTOS PARA EMPRESAS
-- ====================================

-- Procedimiento para registrar empresa
CREATE PROCEDURE sp_RegistrarEmpresa
    @nombre VARCHAR(200),
    @ruc CHAR(11),
    @correo VARCHAR(150),
    @contrasena VARCHAR(255),
    @descripcion VARCHAR(MAX) = NULL,
    @logo VARCHAR(255) = NULL,
    @video_presentacion VARCHAR(255) = NULL
AS
BEGIN
    SET NOCOUNT ON;
    
    BEGIN TRY
        -- Validar RUC
        IF dbo.ValidarRUC(@ruc) = 0
        BEGIN
            RAISERROR('El RUC debe tener 11 dígitos y formato válido.', 16, 1);
            RETURN;
        END
        
        -- Generar hash de contraseña
        DECLARE @hash_contrasena VARCHAR(64);
        SET @hash_contrasena = dbo.GenerarHashContrasena(@contrasena);
        
        -- Insertar empresa
        INSERT INTO Empresas (nombre, ruc, correo, contrasena_hash, descripcion, logo, video_presentacion)
        VALUES (@nombre, @ruc, @correo, @hash_contrasena, @descripcion, @logo, @video_presentacion);
        
        SELECT SCOPE_IDENTITY() as id_empresa, 'Empresa registrada exitosamente.' as mensaje;
        
    END TRY
    BEGIN CATCH
        SELECT ERROR_MESSAGE() as error;
    END CATCH
END
GO

-- Procedimiento para login de empresa
CREATE PROCEDURE sp_LoginEmpresa
    @correo VARCHAR(150),
    @contrasena VARCHAR(255)
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @id_empresa INT, @hash_almacenado VARCHAR(64);
    
    SELECT @id_empresa = id_empresa, @hash_almacenado = contrasena_hash
    FROM Empresas
    WHERE correo = @correo AND activo = 1;
    
    IF @id_empresa IS NULL
    BEGIN
        SELECT 0 as exito, 'Correo no encontrado o cuenta inactiva.' as mensaje;
        RETURN;
    END
    
    IF dbo.ValidarContrasena(@contrasena, @hash_almacenado) = 1
    BEGIN
        SELECT 1 as exito, 'Login exitoso.' as mensaje, @id_empresa as id_empresa;
    END
    ELSE
    BEGIN
        SELECT 0 as exito, 'Contraseña incorrecta.' as mensaje;
    END
END
GO

-- ====================================
-- PROCEDIMIENTOS PARA OFERTAS
-- ====================================

-- Procedimiento para crear oferta laboral
CREATE PROCEDURE sp_CrearOfertaLaboral
    @id_empresa INT,
    @titulo VARCHAR(200),
    @descripcion VARCHAR(MAX),
    @requisitos VARCHAR(MAX) = NULL,
    @salario_min DECIMAL(10,2) = NULL,
    @salario_max DECIMAL(10,2) = NULL,
    @modalidad VARCHAR(50),
    @ubicacion VARCHAR(200) = NULL,
    @fecha_cierre DATETIME = NULL
AS
BEGIN
    SET NOCOUNT ON;
    
    BEGIN TRY
        INSERT INTO OfertasLaborales (id_empresa, titulo, descripcion, requisitos, salario_min, salario_max, modalidad, ubicacion, fecha_cierre)
        VALUES (@id_empresa, @titulo, @descripcion, @requisitos, @salario_min, @salario_max, @modalidad, @ubicacion, @fecha_cierre);
        
        SELECT SCOPE_IDENTITY() as id_oferta, 'Oferta laboral creada exitosamente.' as mensaje;
        
    END TRY
    BEGIN CATCH
        SELECT ERROR_MESSAGE() as error;
    END CATCH
END
GO

-- ====================================
-- PROCEDIMIENTOS PARA POSTULACIONES
-- ====================================

-- Procedimiento para postular a oferta
CREATE PROCEDURE sp_PostularOferta
    @id_estudiante INT,
    @id_oferta INT
AS
BEGIN
    SET NOCOUNT ON;
    
    BEGIN TRY
        -- Verificar que la oferta esté activa
        IF NOT EXISTS (SELECT 1 FROM OfertasLaborales WHERE id_oferta = @id_oferta AND activo = 1)
        BEGIN
            RAISERROR('La oferta laboral no existe o no está activa.', 16, 1);
            RETURN;
        END
        
        -- Verificar que no haya postulado antes
        IF EXISTS (SELECT 1 FROM Postulaciones WHERE id_estudiante = @id_estudiante AND id_oferta = @id_oferta)
        BEGIN
            RAISERROR('Ya has postulado a esta oferta laboral.', 16, 1);
            RETURN;
        END
        
        INSERT INTO Postulaciones (id_estudiante, id_oferta)
        VALUES (@id_estudiante, @id_oferta);
        
        SELECT 'Postulación enviada exitosamente.' as mensaje;
        
    END TRY
    BEGIN CATCH
        SELECT ERROR_MESSAGE() as error;
    END CATCH
END
GO

-- Procedimiento para ver postulaciones de estudiante
CREATE PROCEDURE sp_VerPostulacionesEstudiante
    @id_estudiante INT
AS
BEGIN
    SET NOCOUNT ON;
    
    SELECT 
        p.id_postulacion,
        o.titulo,
        e.nombre as empresa,
        p.fecha_postulacion,
        p.estado,
        o.modalidad,
        o.ubicacion,
        p.comentarios
    FROM Postulaciones p
    INNER JOIN OfertasLaborales o ON p.id_oferta = o.id_oferta
    INNER JOIN Empresas e ON o.id_empresa = e.id_empresa
    WHERE p.id_estudiante = @id_estudiante
    ORDER BY p.fecha_postulacion DESC;
END
GO

-- Procedimiento para ver postulantes de empresa
CREATE PROCEDURE sp_VerPostulantesEmpresa
    @id_empresa INT,
    @id_oferta INT = NULL
AS
BEGIN
    SET NOCOUNT ON;
    
    SELECT 
        p.id_postulacion,
        est.nombres + ' ' + est.apellidos as nombre_completo,
        est.correo,
        est.dni,
        dbo.CalcularEdad(est.anio_nacimiento) as edad,
        o.titulo as oferta,
        p.fecha_postulacion,
        p.estado,
        est.cv_archivo,
        est.foto_perfil
    FROM Postulaciones p
    INNER JOIN Estudiantes est ON p.id_estudiante = est.id_estudiante
    INNER JOIN OfertasLaborales o ON p.id_oferta = o.id_oferta
    WHERE o.id_empresa = @id_empresa
      AND (@id_oferta IS NULL OR o.id_oferta = @id_oferta)
    ORDER BY p.fecha_postulacion DESC;
END
GO

-- ====================================
-- PROCEDIMIENTOS PARA ADMINISTRACIÓN
-- ====================================

-- Procedimiento para estadísticas del admin
CREATE PROCEDURE sp_EstadisticasAdmin
AS
BEGIN
    SET NOCOUNT ON;
    
    -- Resumen general
    SELECT 
        (SELECT COUNT(*) FROM Estudiantes WHERE activo = 1) as total_estudiantes,
        (SELECT COUNT(*) FROM Empresas WHERE activo = 1) as total_empresas,
        (SELECT COUNT(*) FROM OfertasLaborales WHERE activo = 1) as total_ofertas,
        (SELECT COUNT(*) FROM Postulaciones) as total_postulaciones;
    
    -- Top empresas con más postulaciones
    SELECT TOP 5
        e.nombre,
        e.ruc,
        dbo.ContarPostulacionesEmpresa(e.id_empresa) as total_postulaciones
    FROM Empresas e
    WHERE e.activo = 1
    ORDER BY dbo.ContarPostulacionesEmpresa(e.id_empresa) DESC;
    
    -- Top estudiantes con más postulaciones
    SELECT TOP 5
        est.nombres + ' ' + est.apellidos as nombre_completo,
        est.dni,
        dbo.ContarPostulacionesEstudiante(est.id_estudiante) as total_postulaciones
    FROM Estudiantes est
    WHERE est.activo = 1
    ORDER BY dbo.ContarPostulacionesEstudiante(est.id_estudiante) DESC;
END
GO

-- Procedimiento para login de administrador
CREATE PROCEDURE sp_LoginAdmin
    @usuario VARCHAR(50),
    @contrasena VARCHAR(255)
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @id_admin INT, @hash_almacenado VARCHAR(64);
    
    SELECT @id_admin = id_admin, @hash_almacenado = contrasena_hash
    FROM Administradores
    WHERE usuario = @usuario AND activo = 1;
    
    IF @id_admin IS NULL
    BEGIN
        SELECT 0 as exito, 'Usuario no encontrado o cuenta inactiva.' as mensaje;
        RETURN;
    END
    
    IF dbo.ValidarContrasena(@contrasena, @hash_almacenado) = 1
    BEGIN
        SELECT 1 as exito, 'Login exitoso.' as mensaje, @id_admin as id_admin;
    END
    ELSE
    BEGIN
        SELECT 0 as exito, 'Contraseña incorrecta.' as mensaje;
    END
END
GO

PRINT 'Procedimientos almacenados creados exitosamente.';
GO
