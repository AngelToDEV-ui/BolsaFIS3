<?php
// Archivo de reportes simplificado para depuración
session_start();

// Verificar que el usuario esté autenticado y sea admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Acceso no autorizado. Debe ser administrador.'
    ]);
    exit;
}

// Configurar rutas
$basePath = dirname(__DIR__);
$configPath = $basePath . '/includes/config.php';

// Verificar que config.php existe
if (!file_exists($configPath)) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Error: No se encontró config.php en ' . $configPath
    ]);
    exit;
}

require_once $configPath;

if (isset($_POST['action']) && $_POST['action'] == 'generate_reports') {
    try {
        // Crear reporte simple para prueba
        $fechaReporte = date('d/m/Y H:i:s');
        
        // Obtener datos básicos
        $stmt = $db->query("SELECT COUNT(*) as total FROM Estudiantes WHERE activo = 1");
        $totalEstudiantes = $db->fetch($stmt)['total'] ?? 0;
        
        $stmt = $db->query("SELECT COUNT(*) as total FROM Empresas WHERE activo = 1");
        $totalEmpresas = $db->fetch($stmt)['total'] ?? 0;
        
        // Generar HTML simple
        $html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte General - Bolsa FIS</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .header { text-align: center; border-bottom: 2px solid #1e3c72; padding-bottom: 20px; margin-bottom: 30px; }
        .header h1 { color: #1e3c72; margin: 0; }
        .stats { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin: 30px 0; }
        .stat-card { background: #f8f9fa; padding: 20px; border-radius: 8px; text-align: center; border: 1px solid #dee2e6; }
        .stat-number { font-size: 2.5rem; font-weight: bold; color: #1e3c72; margin-bottom: 5px; }
        .stat-label { color: #6c757d; font-size: 1rem; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; padding: 20px; border-radius: 8px; color: #155724; margin: 20px 0; }
        .footer { margin-top: 40px; text-align: center; color: #6c757d; font-size: 0.9rem; border-top: 1px solid #dee2e6; padding-top: 20px; }
        @media print { body { margin: 0; background: white; } .container { box-shadow: none; } }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 REPORTE GENERAL</h1>
            <h2>Bolsa de Trabajo FIS-UNCP</h2>
            <p><strong>Fecha de generación:</strong> ' . $fechaReporte . '</p>
        </div>
        
        <div class="success">
            <h3>✅ Reporte Generado Exitosamente</h3>
            <p>El sistema está funcionando correctamente. Todos los componentes están operativos.</p>
        </div>
        
        <div class="stats">
            <div class="stat-card">
                <div class="stat-number">' . $totalEstudiantes . '</div>
                <div class="stat-label">👨‍🎓 Estudiantes Activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">' . $totalEmpresas . '</div>
                <div class="stat-label">🏢 Empresas Registradas</div>
            </div>
        </div>
        
        <div style="margin: 30px 0; padding: 20px; background: #e9ecef; border-radius: 8px;">
            <h3>📋 Instrucciones</h3>
            <ol>
                <li><strong>Para guardar como PDF:</strong> Presiona Ctrl+P y selecciona "Guardar como PDF"</li>
                <li><strong>Para imprimir:</strong> Presiona Ctrl+P y selecciona tu impresora</li>
                <li><strong>Compartir:</strong> Puedes enviar este enlace o descargar el PDF</li>
            </ol>
        </div>
        
        <div class="footer">
            <p><strong>© 2025 Facultad de Ingeniería de Sistemas - Universidad Nacional del Centro del Perú</strong></p>
            <p>Sistema de Bolsa de Trabajo - Reporte automatizado</p>
        </div>
    </div>
</body>
</html>';

        // Guardar archivo
        $nombreArchivo = 'reporte_simple_' . date('Y-m-d_H-i-s') . '.html';
        $rutaCompleta = __DIR__ . '/' . $nombreArchivo;
        
        $resultado = file_put_contents($rutaCompleta, $html);
        
        if ($resultado === false) {
            throw new Exception('No se pudo escribir el archivo en: ' . $rutaCompleta);
        }
        
        // Respuesta exitosa
        $response = [
            'success' => true,
            'message' => 'Reporte generado correctamente',
            'archivo' => $nombreArchivo,
            'ruta' => 'reports/' . $nombreArchivo,
            'debug' => [
                'estudiantes' => $totalEstudiantes,
                'empresas' => $totalEmpresas,
                'archivo_size' => $resultado,
                'ruta_completa' => $rutaCompleta
            ]
        ];
        
    } catch (Exception $e) {
        $response = [
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
            'debug' => [
                'error_line' => $e->getLine(),
                'error_file' => $e->getFile()
            ]
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

// Si no es POST con action válida
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => 'Método no permitido o acción no válida'
]);
exit;
?>
