-- ===================================
-- CREACIÓN DE BASE DE DATOS
-- Bolsa de Trabajo FIS-UNCP
-- ===================================

USE master;
GO

-- Crear nueva base de datos
CREATE DATABASE BolsaTrabajoFIS
ON 
( NAME = 'BolsaTrabajoFIS_Data',
  FILENAME = 'C:\Program Files\Microsoft SQL Server\MSSQL15.MSSQLSERVER\MSSQL\DATA\BolsaTrabajoFIS.mdf',
  SIZE = 100MB,
  MAXSIZE = 1GB,
  FILEGROWTH = 10MB )
LOG ON 
( NAME = 'BolsaTrabajoFIS_Log',
  FILENAME = 'C:\Program Files\Microsoft SQL Server\MSSQL15.MSSQLSERVER\MSSQL\DATA\BolsaTrabajoFIS.ldf',
  SIZE = 10MB,
  MAXSIZE = 100MB,
  FILEGROWTH = 5MB );
GO

-- Usar la base de datos creada
USE BolsaTrabajoFIS;
GO

PRINT 'Base de datos BolsaTrabajoFIS creada exitosamente.';
GO
