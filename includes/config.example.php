<?php
/**
 * Archivo de configuración de ejemplo para BolsaFIS3
 * 
 * Copia este archivo como 'config.php' y modifica los valores según tu entorno
 */

// Configuración de la base de datos
$serverName = "localhost\\SQLEXPRESS"; // Cambia por tu servidor SQL Server
$database = "BolsaFIS3";               // Nombre de tu base de datos
$username = "tu_usuario";              // Tu usuario de SQL Server
$password = "tu_contraseña";           // Tu contraseña de SQL Server

// Opciones de conexión PDO
$options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
);

try {
    // Crear conexión PDO
    $pdo = new PDO("sqlsrv:Server=$serverName;Database=$database", $username, $password, $options);
    
    // Configurar encoding UTF-8
    $pdo->exec("SET NAMES 'utf8'");
    
} catch(PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Configuración de la aplicación
define('BASE_URL', 'http://localhost/BolsaFIS3');
define('UPLOAD_PATH', __DIR__ . '/../uploads/');
define('MAX_FILE_SIZE', 5 * 1024 * 1024); // 5MB

// Configuración de sesión
session_start();

// Funciones de utilidad para debugging (solo en desarrollo)
function debug($data) {
    if (true) { // Cambia a false en producción
        echo '<pre>';
        print_r($data);
        echo '</pre>';
    }
}
?>
