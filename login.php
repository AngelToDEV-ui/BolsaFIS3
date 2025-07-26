<?php
require_once 'includes/config.php';

$userType = $_GET['type'] ?? '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userType = $_POST['user_type'];
    $username = sanitizeInput($_POST['username']);
    $password = $_POST['password'];
    
    try {
        if ($userType == 'admin') {
            // Login de administrador
            $sql = "SELECT id_admin, usuario, contrasena_hash, nombre_completo 
                    FROM Administradores 
                    WHERE usuario = ? AND activo = 1";
            $stmt = $db->query($sql, [$username]);
            $user = $db->fetch($stmt);
            
            if ($user) {
                // Verificar contraseña usando la función SQL Server
                $sql = "SELECT dbo.ValidarContrasena(?, ?) as valida";
                $stmt = $db->query($sql, [$password, $user['contrasena_hash']]);
                $result = $db->fetch($stmt);
                
                if ($result && $result['valida'] == 1) {
                    $_SESSION['user_id'] = $user['id_admin'];
                    $_SESSION['user_type'] = 'admin';
                    $_SESSION['user_name'] = $user['nombre_completo'];
                    header('Location: admin/dashboard.php');
                    exit;
                } else {
                    $error = 'Usuario o contraseña incorrectos';
                }
            } else {
                $error = 'Usuario o contraseña incorrectos';
            }
            
        } elseif ($userType == 'student') {
            // Login de estudiante
            $sql = "SELECT id_estudiante, nombres, apellidos, dni, correo, contrasena_hash 
                    FROM Estudiantes 
                    WHERE correo = ? AND activo = 1";
            $stmt = $db->query($sql, [$username]);
            $user = $db->fetch($stmt);
            
            if ($user) {
                // Verificar contraseña
                $sql = "SELECT dbo.ValidarContrasena(?, ?) as valida";
                $stmt = $db->query($sql, [$password, $user['contrasena_hash']]);
                $result = $db->fetch($stmt);
                
                if ($result['valida'] == 1) {
                    $_SESSION['user_id'] = $user['id_estudiante'];
                    $_SESSION['user_type'] = 'student';
                    $_SESSION['user_name'] = $user['nombres'] . ' ' . $user['apellidos'];
                    header('Location: student/dashboard.php');
                    exit;
                } else {
                    $error = 'Correo o contraseña incorrectos';
                }
            } else {
                $error = 'Correo o contraseña incorrectos';
            }
            
        } elseif ($userType == 'company') {
            // Login de empresa
            $sql = "SELECT id_empresa, nombre, ruc, correo, contrasena_hash 
                    FROM Empresas 
                    WHERE correo = ? AND activo = 1";
            $stmt = $db->query($sql, [$username]);
            $user = $db->fetch($stmt);
            
            if ($user) {
                // Verificar contraseña
                $sql = "SELECT dbo.ValidarContrasena(?, ?) as valida";
                $stmt = $db->query($sql, [$password, $user['contrasena_hash']]);
                $result = $db->fetch($stmt);
                
                if ($result['valida'] == 1) {
                    $_SESSION['user_id'] = $user['id_empresa'];
                    $_SESSION['user_type'] = 'company';
                    $_SESSION['user_name'] = $user['nombre'];
                    header('Location: company/dashboard.php');
                    exit;
                } else {
                    $error = 'Correo o contraseña incorrectos';
                }
            } else {
                $error = 'Correo o contraseña incorrectos';
            }
        }
    } catch (Exception $e) {
        $error = 'Error del sistema: ' . $e->getMessage();
    }
}

// Determinar el título y placeholder según el tipo de usuario
$titles = [
    'student' => 'Iniciar Sesión - Estudiante',
    'company' => 'Iniciar Sesión - Empresa', 
    'admin' => 'Iniciar Sesión - Administrador'
];

$placeholders = [
    'student' => 'correo@uncp.edu.pe',
    'company' => 'contacto@empresa.com',
    'admin' => 'usuario'
];

$title = $titles[$userType] ?? 'Iniciar Sesión';
$placeholder = $placeholders[$userType] ?? 'Usuario/Correo';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $title; ?> - FIS UNCP</title>
    <link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div style="text-align: center; margin-bottom: 1rem;">
                    <img src="public/images/logo-uncp.png" alt="Logo FIS" class="login-logo">
                </div>
                <h1 class="login-title">FIS-UNCP</h1>
                <p class="login-subtitle"><?php echo $title; ?></p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <form method="POST" action="">
                <input type="hidden" name="user_type" value="<?php echo htmlspecialchars($userType); ?>">
                
                <div class="form-group">
                    <label for="username" class="form-label">
                        <?php echo $userType == 'admin' ? 'Usuario' : 'Correo Electrónico'; ?>
                    </label>
                    <input type="<?php echo $userType == 'admin' ? 'text' : 'email'; ?>" 
                           id="username" 
                           name="username" 
                           class="form-control" 
                           placeholder="<?php echo $placeholder; ?>" 
                           required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Contraseña</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-control" 
                           placeholder="Ingrese su contraseña" 
                           required>
                </div>

                <button type="submit" class="btn" style="width: 100%;">Iniciar Sesión</button>
            </form>

            <div class="text-center mt-3">
                <a href="index.html" style="color: #6c757d; text-decoration: none; font-size: 0.9rem;">
                    ← Volver al inicio
                </a>
            </div>

            <?php if ($userType != 'admin'): ?>
                <div class="text-center mt-3">
                    <p style="color: #6c757d; font-size: 0.9rem;">
                        ¿No tienes cuenta? 
                        <a href="register.php" style="color: #2a5298; text-decoration: none;">Regístrate aquí</a>
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
