-- ===================================
-- CREACIÓN DE FUNCIONES
-- Bolsa de Trabajo FIS-UNCP
-- ===================================

USE BolsaTrabajoFIS;
GO

-- Función para calcular edad desde año de nacimiento
CREATE FUNCTION dbo.CalcularEdad(@anio_nacimiento INT)
RETURNS INT
AS
BEGIN
    DECLARE @edad INT;
    
    -- Como solo tenemos el año, calcular edad simple
    SET @edad = YEAR(GETDATE()) - @anio_nacimiento;
    
    RETURN @edad;
END
GO

-- Función para validar edad mínima (16 años)
CREATE FUNCTION dbo.ValidarEdadMinima(@anio_nacimiento INT)
RETURNS BIT
AS
BEGIN
    DECLARE @edad INT;
    SET @edad = dbo.CalcularEdad(@anio_nacimiento);
    
    IF @edad >= 16
        RETURN 1;
    
    RETURN 0;
END
GO

-- Función para generar hash de contraseña
CREATE FUNCTION dbo.GenerarHashContrasena(@contrasena VARCHAR(255))
RETURNS VARCHAR(64)
AS
BEGIN
    RETURN CONVERT(VARCHAR(64), HASHBYTES('SHA2_256', @contrasena), 2);
END
GO

-- Función para validar contraseña
CREATE FUNCTION dbo.ValidarContrasena(@contrasena VARCHAR(255), @hash_almacenado VARCHAR(64))
RETURNS BIT
AS
BEGIN
    DECLARE @hash_calculado VARCHAR(64);
    SET @hash_calculado = dbo.GenerarHashContrasena(@contrasena);
    
    IF @hash_calculado = @hash_almacenado
        RETURN 1;
    
    RETURN 0;
END
GO

-- Función para contar postulaciones de un estudiante
CREATE FUNCTION dbo.ContarPostulacionesEstudiante(@id_estudiante INT)
RETURNS INT
AS
BEGIN
    DECLARE @total INT;
    
    SELECT @total = COUNT(*)
    FROM Postulaciones
    WHERE id_estudiante = @id_estudiante;
    
    RETURN ISNULL(@total, 0);
END
GO

-- Función para contar postulaciones a ofertas de una empresa
CREATE FUNCTION dbo.ContarPostulacionesEmpresa(@id_empresa INT)
RETURNS INT
AS
BEGIN
    DECLARE @total INT;
    
    SELECT @total = COUNT(p.id_postulacion)
    FROM Postulaciones p
    INNER JOIN OfertasLaborales o ON p.id_oferta = o.id_oferta
    WHERE o.id_empresa = @id_empresa;
    
    RETURN ISNULL(@total, 0);
END
GO

-- Función para obtener estadísticas de ofertas por empresa
CREATE FUNCTION dbo.EstadisticasOfertasEmpresa(@id_empresa INT)
RETURNS TABLE
AS
RETURN
(
    SELECT 
        COUNT(o.id_oferta) as total_ofertas,
        COUNT(CASE WHEN o.activo = 1 THEN 1 END) as ofertas_activas,
        COUNT(p.id_postulacion) as total_postulaciones,
        COUNT(CASE WHEN p.estado = 'Seleccionado' THEN 1 END) as seleccionados
    FROM OfertasLaborales o
    LEFT JOIN Postulaciones p ON o.id_oferta = p.id_oferta
    WHERE o.id_empresa = @id_empresa
);
GO

-- Función para validar formato de RUC
CREATE FUNCTION dbo.ValidarRUC(@ruc VARCHAR(11))
RETURNS BIT
AS
BEGIN
    -- Verificar que tiene exactamente 11 dígitos
    IF LEN(@ruc) <> 11 OR @ruc NOT LIKE '[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]'
        RETURN 0;
    
    -- Verificar que comience con 10 o 20 (empresas en Perú)
    IF LEFT(@ruc, 2) NOT IN ('10', '20')
        RETURN 0;
    
    RETURN 1;
END
GO

-- Función para validar formato de DNI
CREATE FUNCTION dbo.ValidarDNI(@dni VARCHAR(8))
RETURNS BIT
AS
BEGIN
    -- Verificar que tiene exactamente 8 dígitos
    IF LEN(@dni) <> 8 OR @dni NOT LIKE '[0-9][0-9][0-9][0-9][0-9][0-9][0-9][0-9]'
        RETURN 0;
    
    -- Verificar que no comience con 00
    IF LEFT(@dni, 2) = '00'
        RETURN 0;
    
    RETURN 1;
END
GO

PRINT 'Funciones creadas exitosamente.';
GO
