<?php
// Verificar duplicados vía AJAX para validaciones en tiempo real
require_once 'includes/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

$response = ['exists' => false];

try {
    if (isset($_POST['email'])) {
        $email = sanitizeInput($_POST['email']);
        $type = $_POST['type'] ?? '';
        
        if ($type === 'student') {
            $sql = "SELECT COUNT(*) as count FROM Estudiantes WHERE correo = ?";
        } elseif ($type === 'company') {
            $sql = "SELECT COUNT(*) as count FROM Empresas WHERE correo = ?";
        } else {
            // Verificar en ambas tablas
            $sql1 = "SELECT COUNT(*) as count FROM Estudiantes WHERE correo = ?";
            $sql2 = "SELECT COUNT(*) as count FROM Empresas WHERE correo = ?";
            
            $stmt1 = $db->query($sql1, [$email]);
            $result1 = $db->fetch($stmt1);
            
            $stmt2 = $db->query($sql2, [$email]);
            $result2 = $db->fetch($stmt2);
            
            $response['exists'] = ($result1['count'] > 0 || $result2['count'] > 0);
            echo json_encode($response);
            exit;
        }
        
        $stmt = $db->query($sql, [$email]);
        $result = $db->fetch($stmt);
        $response['exists'] = ($result['count'] > 0);
        
    } elseif (isset($_POST['dni'])) {
        $dni = sanitizeInput($_POST['dni']);
        
        $sql = "SELECT COUNT(*) as count FROM Estudiantes WHERE dni = ?";
        $stmt = $db->query($sql, [$dni]);
        $result = $db->fetch($stmt);
        $response['exists'] = ($result['count'] > 0);
        
    } elseif (isset($_POST['ruc'])) {
        $ruc = sanitizeInput($_POST['ruc']);
        
        $sql = "SELECT COUNT(*) as count FROM Empresas WHERE ruc = ?";
        $stmt = $db->query($sql, [$ruc]);
        $result = $db->fetch($stmt);
        $response['exists'] = ($result['count'] > 0);
        
    } else {
        $response['error'] = 'Parámetros inválidos';
    }
    
} catch (Exception $e) {
    $response['error'] = 'Error del servidor';
    $response['exists'] = false;
    error_log("Error en check_duplicates.php: " . $e->getMessage());
}

echo json_encode($response);
exit;
?>
