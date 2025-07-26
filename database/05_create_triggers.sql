-- ===================================
-- CREACIÓN DE TRIGGERS
-- Bolsa de Trabajo FIS-UNCP
-- ===================================

USE BolsaTrabajoFIS;
GO

-- ====================================
-- TRIGGERS PARA ESTUDIANTES
-- ====================================

-- Trigger para validar edad antes de insertar estudiante
CREATE TRIGGER tr_ValidarEdadEstudiante
ON Estudiantes
INSTEAD OF INSERT
AS
BEGIN
    SET NOCOUNT ON;
    
    -- Validar cada registro insertado
    DECLARE @nombres VARCHAR(100), @apellidos VARCHAR(100), @dni CHAR(8), 
            @correo VARCHAR(150), @anio_nacimiento INT, @contrasena_hash VARCHAR(64),
            @foto_perfil VARCHAR(255), @cv_archivo VARCHAR(255);
    
    DECLARE estudiante_cursor CURSOR FOR
    SELECT nombres, apellidos, dni, correo, anio_nacimiento, contrasena_hash, foto_perfil, cv_archivo
    FROM inserted;
    
    OPEN estudiante_cursor;
    FETCH NEXT FROM estudiante_cursor INTO @nombres, @apellidos, @dni, @correo, @anio_nacimiento, @contrasena_hash, @foto_perfil, @cv_archivo;
    
    WHILE @@FETCH_STATUS = 0
    BEGIN
        -- Validar edad mínima
        IF dbo.ValidarEdadMinima(@anio_nacimiento) = 0
        BEGIN
            RAISERROR('Error: La edad mínima permitida es 16 años para el DNI %s.', 16, 1, @dni);
            ROLLBACK TRANSACTION;
            RETURN;
        END
        
        -- Validar DNI
        IF dbo.ValidarDNI(@dni) = 0
        BEGIN
            RAISERROR('Error: El DNI %s no tiene un formato válido.', 16, 1, @dni);
            ROLLBACK TRANSACTION;
            RETURN;
        END
        
        -- Insertar si todas las validaciones pasan
        INSERT INTO Estudiantes (nombres, apellidos, dni, correo, anio_nacimiento, contrasena_hash, foto_perfil, cv_archivo)
        VALUES (@nombres, @apellidos, @dni, @correo, @anio_nacimiento, @contrasena_hash, @foto_perfil, @cv_archivo);
        
        FETCH NEXT FROM estudiante_cursor INTO @nombres, @apellidos, @dni, @correo, @anio_nacimiento, @contrasena_hash, @foto_perfil, @cv_archivo;
    END
    
    CLOSE estudiante_cursor;
    DEALLOCATE estudiante_cursor;
END
GO

-- Trigger para evitar modificación de DNI y año de nacimiento
CREATE TRIGGER tr_ProtegerDatosEstudiante
ON Estudiantes
FOR UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    
    -- Verificar si se intenta modificar DNI o año de nacimiento
    IF UPDATE(dni) OR UPDATE(anio_nacimiento)
    BEGIN
        RAISERROR('No se puede modificar el DNI o año de nacimiento del estudiante.', 16, 1);
        ROLLBACK TRANSACTION;
        RETURN;
    END
    
    -- Si se actualiza el año de nacimiento (aunque no debería), validar edad
    IF EXISTS (
        SELECT 1 
        FROM inserted i
        WHERE dbo.ValidarEdadMinima(i.anio_nacimiento) = 0
    )
    BEGIN
        RAISERROR('La edad mínima permitida es 16 años.', 16, 1);
        ROLLBACK TRANSACTION;
        RETURN;
    END
END
GO

-- ====================================
-- TRIGGERS PARA EMPRESAS
-- ====================================

-- Trigger para validar RUC antes de insertar empresa
CREATE TRIGGER tr_ValidarRUCEmpresa
ON Empresas
INSTEAD OF INSERT
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @nombre VARCHAR(200), @ruc CHAR(11), @correo VARCHAR(150), 
            @contrasena_hash VARCHAR(64), @descripcion VARCHAR(MAX), @logo VARCHAR(255), 
            @video_presentacion VARCHAR(255);
    
    DECLARE empresa_cursor CURSOR FOR
    SELECT nombre, ruc, correo, contrasena_hash, descripcion, logo, video_presentacion
    FROM inserted;
    
    OPEN empresa_cursor;
    FETCH NEXT FROM empresa_cursor INTO @nombre, @ruc, @correo, @contrasena_hash, @descripcion, @logo, @video_presentacion;
    
    WHILE @@FETCH_STATUS = 0
    BEGIN
        -- Validar RUC
        IF dbo.ValidarRUC(@ruc) = 0
        BEGIN
            RAISERROR('Error: El RUC %s no tiene un formato válido.', 16, 1, @ruc);
            ROLLBACK TRANSACTION;
            RETURN;
        END
        
        -- Insertar si todas las validaciones pasan
        INSERT INTO Empresas (nombre, ruc, correo, contrasena_hash, descripcion, logo, video_presentacion)
        VALUES (@nombre, @ruc, @correo, @contrasena_hash, @descripcion, @logo, @video_presentacion);
        
        FETCH NEXT FROM empresa_cursor INTO @nombre, @ruc, @correo, @contrasena_hash, @descripcion, @logo, @video_presentacion;
    END
    
    CLOSE empresa_cursor;
    DEALLOCATE empresa_cursor;
END
GO

-- Trigger para evitar modificación de RUC
CREATE TRIGGER tr_ProtegerRUCEmpresa
ON Empresas
FOR UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    
    -- Verificar si se intenta modificar RUC
    IF UPDATE(ruc)
    BEGIN
        RAISERROR('No se puede modificar el RUC de la empresa.', 16, 1);
        ROLLBACK TRANSACTION;
        RETURN;
    END
END
GO

-- ====================================
-- TRIGGERS PARA OFERTAS LABORALES
-- ====================================

-- Trigger para auditoría de ofertas laborales
CREATE TRIGGER tr_AuditoriaOfertas
ON OfertasLaborales
FOR INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @accion VARCHAR(20);
    
    -- Determinar el tipo de acción
    IF EXISTS (SELECT * FROM inserted) AND NOT EXISTS (SELECT * FROM deleted)
        SET @accion = 'INSERT';
    ELSE IF EXISTS (SELECT * FROM inserted) AND EXISTS (SELECT * FROM deleted)
        SET @accion = 'UPDATE';
    ELSE
        SET @accion = 'DELETE';
    
    -- Registrar la auditoría para inserciones y actualizaciones
    IF @accion IN ('INSERT', 'UPDATE')
    BEGIN
        INSERT INTO AuditoriaOfertas (id_oferta, id_empresa, accion)
        SELECT id_oferta, id_empresa, @accion
        FROM inserted;
    END
    
    -- Registrar la auditoría para eliminaciones
    IF @accion = 'DELETE'
    BEGIN
        INSERT INTO AuditoriaOfertas (id_oferta, id_empresa, accion)
        SELECT id_oferta, id_empresa, @accion
        FROM deleted;
    END
END
GO

-- Trigger para validar fechas de ofertas
CREATE TRIGGER tr_ValidarFechasOferta
ON OfertasLaborales
FOR INSERT, UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    
    -- Validar que la fecha de cierre sea posterior a la fecha de publicación
    IF EXISTS (
        SELECT 1 
        FROM inserted 
        WHERE fecha_cierre IS NOT NULL 
          AND fecha_cierre <= fecha_publicacion
    )
    BEGIN
        RAISERROR('La fecha de cierre debe ser posterior a la fecha de publicación.', 16, 1);
        ROLLBACK TRANSACTION;
        RETURN;
    END
    
    -- Validar que la fecha de cierre no sea en el pasado para nuevas ofertas
    IF EXISTS (
        SELECT 1 
        FROM inserted 
        WHERE fecha_cierre IS NOT NULL 
          AND fecha_cierre < GETDATE()
          AND NOT EXISTS (SELECT 1 FROM deleted WHERE deleted.id_oferta = inserted.id_oferta)
    )
    BEGIN
        RAISERROR('La fecha de cierre no puede ser en el pasado.', 16, 1);
        ROLLBACK TRANSACTION;
        RETURN;
    END
END
GO

-- ====================================
-- TRIGGERS PARA POSTULACIONES
-- ====================================

-- Trigger para validar postulaciones
CREATE TRIGGER tr_ValidarPostulacion
ON Postulaciones
FOR INSERT
AS
BEGIN
    SET NOCOUNT ON;
    
    -- Verificar que la oferta esté activa
    IF EXISTS (
        SELECT 1 
        FROM inserted i
        INNER JOIN OfertasLaborales o ON i.id_oferta = o.id_oferta
        WHERE o.activo = 0
    )
    BEGIN
        RAISERROR('No se puede postular a una oferta inactiva.', 16, 1);
        ROLLBACK TRANSACTION;
        RETURN;
    END
    
    -- Verificar que la oferta no haya cerrado
    IF EXISTS (
        SELECT 1 
        FROM inserted i
        INNER JOIN OfertasLaborales o ON i.id_oferta = o.id_oferta
        WHERE o.fecha_cierre IS NOT NULL AND o.fecha_cierre < GETDATE()
    )
    BEGIN
        RAISERROR('No se puede postular a una oferta que ya ha cerrado.', 16, 1);
        ROLLBACK TRANSACTION;
        RETURN;
    END
    
    -- Verificar que el estudiante esté activo
    IF EXISTS (
        SELECT 1 
        FROM inserted i
        INNER JOIN Estudiantes e ON i.id_estudiante = e.id_estudiante
        WHERE e.activo = 0
    )
    BEGIN
        RAISERROR('Solo estudiantes activos pueden postular.', 16, 1);
        ROLLBACK TRANSACTION;
        RETURN;
    END
END
GO

-- Trigger para evitar cambios no autorizados en postulaciones
CREATE TRIGGER tr_ProtegerPostulacion
ON Postulaciones
FOR UPDATE
AS
BEGIN
    SET NOCOUNT ON;
    
    -- Solo permitir cambios en estado y comentarios
    IF UPDATE(id_estudiante) OR UPDATE(id_oferta) OR UPDATE(fecha_postulacion)
    BEGIN
        RAISERROR('Solo se puede modificar el estado y comentarios de una postulación.', 16, 1);
        ROLLBACK TRANSACTION;
        RETURN;
    END
    
    -- Validar transiciones de estado válidas
    IF EXISTS (
        SELECT 1 
        FROM inserted i
        INNER JOIN deleted d ON i.id_postulacion = d.id_postulacion
        WHERE (d.estado = 'Seleccionado' AND i.estado != 'Seleccionado') OR
              (d.estado = 'Rechazado' AND i.estado != 'Rechazado')
    )
    BEGIN
        RAISERROR('No se puede cambiar el estado de una postulación seleccionada o rechazada.', 16, 1);
        ROLLBACK TRANSACTION;
        RETURN;
    END
END
GO

-- ====================================
-- TRIGGER PARA LOG DE ACTIVIDAD
-- ====================================

-- Crear tabla para log de actividad general
CREATE TABLE LogActividad (
    id_log INT IDENTITY(1,1) PRIMARY KEY,
    tabla VARCHAR(50) NOT NULL,
    operacion VARCHAR(20) NOT NULL,
    usuario_sistema VARCHAR(100) DEFAULT SYSTEM_USER,
    fecha DATETIME DEFAULT GETDATE(),
    detalle VARCHAR(500) NULL
);
GO

-- Trigger para log general de actividad
CREATE TRIGGER tr_LogActividad_Estudiantes
ON Estudiantes
FOR INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @operacion VARCHAR(20), @detalle VARCHAR(500);
    
    IF EXISTS (SELECT * FROM inserted) AND NOT EXISTS (SELECT * FROM deleted)
    BEGIN
        SET @operacion = 'INSERT';
        SELECT @detalle = 'Nuevo estudiante: ' + nombres + ' ' + apellidos + ' (DNI: ' + dni + ')'
        FROM inserted;
    END
    ELSE IF EXISTS (SELECT * FROM inserted) AND EXISTS (SELECT * FROM deleted)
    BEGIN
        SET @operacion = 'UPDATE';
        SELECT @detalle = 'Actualización estudiante DNI: ' + dni
        FROM inserted;
    END
    ELSE
    BEGIN
        SET @operacion = 'DELETE';
        SELECT @detalle = 'Eliminación estudiante: ' + nombres + ' ' + apellidos + ' (DNI: ' + dni + ')'
        FROM deleted;
    END
    
    INSERT INTO LogActividad (tabla, operacion, detalle)
    VALUES ('Estudiantes', @operacion, @detalle);
END
GO

CREATE TRIGGER tr_LogActividad_Empresas
ON Empresas
FOR INSERT, UPDATE, DELETE
AS
BEGIN
    SET NOCOUNT ON;
    
    DECLARE @operacion VARCHAR(20), @detalle VARCHAR(500);
    
    IF EXISTS (SELECT * FROM inserted) AND NOT EXISTS (SELECT * FROM deleted)
    BEGIN
        SET @operacion = 'INSERT';
        SELECT @detalle = 'Nueva empresa: ' + nombre + ' (RUC: ' + ruc + ')'
        FROM inserted;
    END
    ELSE IF EXISTS (SELECT * FROM inserted) AND EXISTS (SELECT * FROM deleted)
    BEGIN
        SET @operacion = 'UPDATE';
        SELECT @detalle = 'Actualización empresa RUC: ' + ruc
        FROM inserted;
    END
    ELSE
    BEGIN
        SET @operacion = 'DELETE';
        SELECT @detalle = 'Eliminación empresa: ' + nombre + ' (RUC: ' + ruc + ')'
        FROM deleted;
    END
    
    INSERT INTO LogActividad (tabla, operacion, detalle)
    VALUES ('Empresas', @operacion, @detalle);
END
GO

PRINT 'Triggers creados exitosamente.';
GO
