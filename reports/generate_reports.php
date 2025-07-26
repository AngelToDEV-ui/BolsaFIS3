<?php
// Configurar la ruta absoluta correcta
$configPath = dirname(__DIR__) . '/includes/config.php';
if (!file_exists($configPath)) {
    // Intentar ruta alternativa
    $configPath = __DIR__ . '/../includes/config.php';
    if (!file_exists($configPath)) {
        die(json_encode(['success' => false, 'message' => 'No se pudo encontrar config.php']));
    }
}
require_once $configPath;

// Verificar que el usuario esté autenticado y sea admin
if (!isLoggedIn() || $_SESSION['user_type'] !== 'admin') {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Acceso no autorizado'
    ]);
    exit;
}

// Por ahora vamos a usar una alternativa simple sin composer
// Generaremos reportes HTML que se pueden imprimir como PDF desde el navegador

if (isset($_POST['action']) && $_POST['action'] == 'generate_reports') {
    try {
        // Log para depuración
        error_log("Iniciando generación de reportes...");
        
        // Obtener datos para los reportes
        
        // Reporte de Estudiantes
        $sql = "SELECT e.nombres, e.apellidos, e.dni, e.correo, e.anio_nacimiento, 
                       e.fecha_registro, COUNT(p.id_postulacion) as total_postulaciones
                FROM Estudiantes e
                LEFT JOIN Postulaciones p ON e.id_estudiante = p.id_estudiante
                WHERE e.activo = 1
                GROUP BY e.id_estudiante, e.nombres, e.apellidos, e.dni, e.correo, 
                         e.anio_nacimiento, e.fecha_registro
                ORDER BY e.nombres, e.apellidos";
        $stmt = $db->query($sql);
        $estudiantes = $db->fetchAll($stmt);
        
        error_log("Estudiantes obtenidos: " . count($estudiantes));
        
        // Reporte de Empresas
        $sql = "SELECT e.nombre, e.ruc, e.correo, e.descripcion, e.fecha_registro,
                       COUNT(o.id_oferta) as total_ofertas,
                       COUNT(p.id_postulacion) as total_postulaciones
                FROM Empresas e
                LEFT JOIN OfertasLaborales o ON e.id_empresa = o.id_empresa
                LEFT JOIN Postulaciones p ON o.id_oferta = p.id_oferta
                WHERE e.activo = 1
                GROUP BY e.id_empresa, e.nombre, e.ruc, e.correo, e.descripcion, e.fecha_registro
                ORDER BY e.nombre";
        $stmt = $db->query($sql);
        $empresas = $db->fetchAll($stmt);
        
        error_log("Empresas obtenidas: " . count($empresas));
        
        // Estadísticas generales
        $stmt = $db->query("SELECT COUNT(*) as total FROM Estudiantes WHERE activo = 1");
        $totalEstudiantes = $db->fetch($stmt)['total'];
        
        $stmt = $db->query("SELECT COUNT(*) as total FROM Empresas WHERE activo = 1");
        $totalEmpresas = $db->fetch($stmt)['total'];
        
        $stmt = $db->query("SELECT COUNT(*) as total FROM OfertasLaborales WHERE activo = 1");
        $totalOfertas = $db->fetch($stmt)['total'];
        
        $stmt = $db->query("SELECT COUNT(*) as total FROM Postulaciones");
        $totalPostulaciones = $db->fetch($stmt)['total'];
        
        $fechaReporte = date('d/m/Y H:i:s');
        
        error_log("Estadísticas: E=$totalEstudiantes, Em=$totalEmpresas, O=$totalOfertas, P=$totalPostulaciones");
        
        // Generar HTML para el reporte
        $reportHtml = generarReporteHTML($estudiantes, $empresas, $totalEstudiantes, $totalEmpresas, $totalOfertas, $totalPostulaciones, $fechaReporte);
        
        error_log("HTML generado, longitud: " . strlen($reportHtml));
        
        // Guardar el reporte en un archivo
        $nombreArchivo = 'reporte_general_' . date('Y-m-d_H-i-s') . '.html';
        $rutaArchivo = __DIR__ . '/' . $nombreArchivo;
        
        $bytesWritten = file_put_contents($rutaArchivo, $reportHtml);
        error_log("Archivo guardado: $rutaArchivo, bytes: $bytesWritten");
        
        if ($bytesWritten === false) {
            throw new Exception("No se pudo escribir el archivo de reporte");
        }
        
        // Responder con éxito
        $response = [
            'success' => true,
            'message' => 'Reportes generados exitosamente',
            'archivo' => $nombreArchivo,
            'ruta' => 'reports/' . $nombreArchivo,
            'debug' => [
                'estudiantes' => count($estudiantes),
                'empresas' => count($empresas),
                'archivo_size' => $bytesWritten
            ]
        ];
        
        error_log("Respuesta preparada: " . json_encode($response));
        
    } catch (Exception $e) {
        error_log("Error en generación de reportes: " . $e->getMessage());
        $response = [
            'success' => false,
            'message' => 'Error al generar reportes: ' . $e->getMessage()
        ];
    }
    
    header('Content-Type: application/json');
    echo json_encode($response);
    exit;
}

function generarReporteHTML($estudiantes, $empresas, $totalEstudiantes, $totalEmpresas, $totalOfertas, $totalPostulaciones, $fechaReporte) {
    
    // Función auxiliar para formatear fechas desde SQL Server
    function formatearFecha($fecha) {
        if ($fecha instanceof DateTime) {
            return $fecha->format('d/m/Y');
        } elseif (is_string($fecha)) {
            return date('d/m/Y', strtotime($fecha));
        } else {
            return 'N/A';
        }
    }
    $html = '<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte General - Bolsa de Trabajo FIS-UNCP</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #1e3c72;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #1e3c72;
            margin: 0;
        }
        .stats {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .stat-card {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #dee2e6;
        }
        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #1e3c72;
        }
        .stat-label {
            color: #6c757d;
            font-size: 0.9rem;
        }
        .section {
            margin-bottom: 40px;
        }
        .section h2 {
            color: #1e3c72;
            border-bottom: 1px solid #dee2e6;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            border: 1px solid #dee2e6;
            padding: 8px;
            text-align: left;
            font-size: 0.9rem;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
            color: #495057;
        }
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            color: #6c757d;
            font-size: 0.8rem;
        }
        @media print {
            body { margin: 10px; }
            .stats { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>REPORTE GENERAL</h1>
        <h2>Bolsa de Trabajo FIS-UNCP</h2>
        <p>Fecha de generación: ' . $fechaReporte . '</p>
    </div>
    
    <div class="stats">
        <div class="stat-card">
            <div class="stat-number">' . $totalEstudiantes . '</div>
            <div class="stat-label">Estudiantes Activos</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">' . $totalEmpresas . '</div>
            <div class="stat-label">Empresas Registradas</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">' . $totalOfertas . '</div>
            <div class="stat-label">Ofertas Laborales</div>
        </div>
        <div class="stat-card">
            <div class="stat-number">' . $totalPostulaciones . '</div>
            <div class="stat-label">Total Postulaciones</div>
        </div>
    </div>';

    // Reporte de Estudiantes
    $html .= '<div class="section">
        <h2>📚 ESTUDIANTES REGISTRADOS</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombres y Apellidos</th>
                    <th>DNI</th>
                    <th>Correo</th>
                    <th>Edad</th>
                    <th>Fecha Registro</th>
                    <th>Postulaciones</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($estudiantes as $estudiante) {
        $edad = date('Y') - $estudiante['anio_nacimiento'];
        $fechaRegistro = formatearFecha($estudiante['fecha_registro']);
        
        $html .= '<tr>
            <td>' . htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']) . '</td>
            <td>' . $estudiante['dni'] . '</td>
            <td>' . htmlspecialchars($estudiante['correo']) . '</td>
            <td>' . $edad . ' años</td>
            <td>' . $fechaRegistro . '</td>
            <td>' . $estudiante['total_postulaciones'] . '</td>
        </tr>';
    }
    
    $html .= '</tbody>
        </table>
    </div>';

    // Reporte de Empresas
    $html .= '<div class="section">
        <h2>🏢 EMPRESAS REGISTRADAS</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>RUC</th>
                    <th>Correo</th>
                    <th>Fecha Registro</th>
                    <th>Ofertas</th>
                    <th>Postulaciones</th>
                </tr>
            </thead>
            <tbody>';
    
    foreach ($empresas as $empresa) {
        $fechaRegistro = formatearFecha($empresa['fecha_registro']);
        
        $html .= '<tr>
            <td>' . htmlspecialchars($empresa['nombre']) . '</td>
            <td>' . $empresa['ruc'] . '</td>
            <td>' . htmlspecialchars($empresa['correo']) . '</td>
            <td>' . $fechaRegistro . '</td>
            <td>' . $empresa['total_ofertas'] . '</td>
            <td>' . $empresa['total_postulaciones'] . '</td>
        </tr>';
    }
    
    $html .= '</tbody>
        </table>
    </div>';

    $html .= '<div class="footer">
        <p>© 2025 Facultad de Ingeniería de Sistemas - Universidad Nacional del Centro del Perú</p>
        <p>Reporte generado automáticamente por el Sistema de Bolsa de Trabajo</p>
    </div>
</body>
</html>';

    return $html;
}

// Si no se envió una acción válida, devolver error
if (!isset($_POST['action'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'No se especificó una acción válida'
    ]);
    exit;
}
?>
