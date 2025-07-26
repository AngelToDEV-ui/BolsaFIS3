<?php
require_once 'includes/config.php';

$userType = $_GET['type'] ?? 'student';
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $userType = $_POST['user_type'];
    
    try {
        if ($userType == 'student') {
            // Registro de estudiante
            $nombres = sanitizeInput($_POST['nombres']);
            $apellidos = sanitizeInput($_POST['apellidos']);
            $dni = sanitizeInput($_POST['dni']);
            $correo = sanitizeInput($_POST['correo']);
            $anio_nacimiento = intval($_POST['anio_nacimiento']);
            $contrasena = $_POST['contrasena'];
            
            // Validaciones
            if (!validateDNI($dni)) {
                throw new Exception('DNI inválido');
            }
            
            if (!validateEmail($correo)) {
                throw new Exception('Correo inválido');
            }
            
            if (calculateAge($anio_nacimiento) < 16) {
                throw new Exception('Debe tener al menos 16 años');
            }
            
            // Manejar archivos opcionales
            $foto_perfil = null;
            $cv_archivo = null;
            
            if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['size'] > 0) {
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                $foto_perfil = uploadFile($_FILES['foto_perfil'], $allowedTypes, 'photos');
            }
            
            if (isset($_FILES['cv_archivo']) && $_FILES['cv_archivo']['size'] > 0) {
                $allowedTypes = ['application/pdf'];
                $cv_archivo = uploadFile($_FILES['cv_archivo'], $allowedTypes, 'cvs');
            }
            
            // Verificar si el correo ya existe antes de intentar registrar
            $checkEmailSql = "SELECT COUNT(*) as count FROM Estudiantes WHERE correo = ?";
            $stmt = $db->query($checkEmailSql, [$correo]);
            $result = $db->fetch($stmt);
            if ($result['count'] > 0) {
                throw new Exception('El correo electrónico ya está registrado. Por favor, usa otro correo.');
            }
            
            // Verificar si el DNI ya existe
            $checkDniSql = "SELECT COUNT(*) as count FROM Estudiantes WHERE dni = ?";
            $stmt = $db->query($checkDniSql, [$dni]);
            $result = $db->fetch($stmt);
            if ($result['count'] > 0) {
                throw new Exception('El DNI ya está registrado. Por favor, verifica tus datos.');
            }
            
            // Llamar al procedimiento almacenado
            $sql = "EXEC sp_RegistrarEstudiante ?, ?, ?, ?, ?, ?, ?, ?";
            $params = [$nombres, $apellidos, $dni, $correo, $anio_nacimiento, $contrasena, $foto_perfil, $cv_archivo];
            
            $db->execute($sql, $params);
            $success = 'Estudiante registrado exitosamente. Ahora puede iniciar sesión.';
            
        } elseif ($userType == 'company') {
            // Registro de empresa
            $nombre = sanitizeInput($_POST['nombre']);
            $ruc = sanitizeInput($_POST['ruc']);
            $correo = sanitizeInput($_POST['correo']);
            $contrasena = $_POST['contrasena'];
            $descripcion = sanitizeInput($_POST['descripcion']);
            
            // Validaciones
            if (!validateRUC($ruc)) {
                throw new Exception('RUC inválido');
            }
            
            if (!validateEmail($correo)) {
                throw new Exception('Correo inválido');
            }
            
            // Manejar archivos opcionales
            $logo = null;
            $video_presentacion = null;
            
            if (isset($_FILES['logo']) && $_FILES['logo']['size'] > 0) {
                $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
                $logo = uploadFile($_FILES['logo'], $allowedTypes, 'logos');
            }
            
            if (isset($_FILES['video_presentacion']) && $_FILES['video_presentacion']['size'] > 0) {
                $allowedTypes = ['video/mp4', 'video/mpeg', 'video/quicktime'];
                $video_presentacion = uploadFile($_FILES['video_presentacion'], $allowedTypes, 'videos');
            }
            
            // Verificar si el correo ya existe antes de intentar registrar
            $checkEmailSql = "SELECT COUNT(*) as count FROM Empresas WHERE correo = ?";
            $stmt = $db->query($checkEmailSql, [$correo]);
            $result = $db->fetch($stmt);
            if ($result['count'] > 0) {
                throw new Exception('El correo electrónico ya está registrado. Por favor, usa otro correo.');
            }
            
            // Verificar si el RUC ya existe
            $checkRucSql = "SELECT COUNT(*) as count FROM Empresas WHERE ruc = ?";
            $stmt = $db->query($checkRucSql, [$ruc]);
            $result = $db->fetch($stmt);
            if ($result['count'] > 0) {
                throw new Exception('El RUC ya está registrado. Por favor, verifica tus datos.');
            }
            
            // Llamar al procedimiento almacenado
            $sql = "EXEC sp_RegistrarEmpresa ?, ?, ?, ?, ?, ?, ?";
            $params = [$nombre, $ruc, $correo, $contrasena, $descripcion, $logo, $video_presentacion];
            
            $db->execute($sql, $params);
            $success = 'Empresa registrada exitosamente. Ahora puede iniciar sesión.';
        }
        
    } catch (Exception $e) {
        // Mejorar el manejo de errores específicos
        $errorMessage = $e->getMessage();
        
        // Verificar si es un error de SQL Server específico
        if (strpos($errorMessage, 'UNIQUE KEY') !== false || strpos($errorMessage, 'duplicate') !== false) {
            if (strpos($errorMessage, 'correo') !== false || strpos($errorMessage, 'email') !== false) {
                $error = 'El correo electrónico ya está registrado. Por favor, usa otro correo.';
            } elseif (strpos($errorMessage, 'dni') !== false) {
                $error = 'El DNI ya está registrado. Por favor, verifica tus datos.';
            } elseif (strpos($errorMessage, 'ruc') !== false) {
                $error = 'El RUC ya está registrado. Por favor, verifica tus datos.';
            } else {
                $error = 'Los datos ingresados ya están registrados en el sistema.';
            }
        } elseif (strpos($errorMessage, 'constraint') !== false) {
            $error = 'Error en los datos ingresados. Por favor, verifica la información.';
        } else {
            $error = $errorMessage;
        }
        
        // Log del error para depuración
        error_log("Error en registro: " . $e->getMessage());
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - FIS UNCP</title>
    <link rel="stylesheet" href="public/css/styles.css">
</head>
<body>
    <div class="container">
        <div class="content-wrapper">
            <div class="text-center mb-3">
                <div style="text-align: center; margin-bottom: 1rem;">
                    <img src="public/images/logo-uncp.png" alt="Logo FIS" class="login-logo">
                </div>
                <h1>Registro - Bolsa de Trabajo FIS</h1>
                <p>Selecciona el tipo de cuenta que deseas crear</p>
            </div>

            <!-- Selector de tipo de usuario -->
            <div class="text-center mb-3">
                <div style="display: inline-flex; gap: 1rem; margin-bottom: 2rem;">
                    <a href="?type=student" class="btn <?php echo $userType == 'student' ? '' : 'btn-secondary'; ?>">
                        Estudiante
                    </a>
                    <a href="?type=company" class="btn <?php echo $userType == 'company' ? '' : 'btn-secondary'; ?>">
                        Empresa
                    </a>
                </div>
            </div>

            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="card">
                <?php if ($userType == 'student'): ?>
                    <!-- Formulario de registro de estudiante -->
                    <div class="card-header">
                        <h2 class="card-title">Registro de Estudiante</h2>
                    </div>

                    <form method="POST" enctype="multipart/form-data" id="studentRegistrationForm">
                        <input type="hidden" name="user_type" value="student">
                        
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="nombres" class="form-label">Nombres *</label>
                                    <input type="text" id="nombres" name="nombres" class="form-control" required>
                                    <div class="form-text">Solo letras y espacios</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="apellidos" class="form-label">Apellidos *</label>
                                    <input type="text" id="apellidos" name="apellidos" class="form-control" required>
                                    <div class="form-text">Solo letras y espacios</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="dni" class="form-label">DNI *</label>
                                    <input type="text" id="dni" name="dni" class="form-control" maxlength="8" required>
                                    <div class="form-text">8 dígitos, no puede comenzar con 00</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="anio_nacimiento" class="form-label">Año de Nacimiento *</label>
                                    <input type="number" id="anio_nacimiento" name="anio_nacimiento" class="form-control" 
                                           min="1900" max="<?php echo date('Y'); ?>" required>
                                    <div class="form-text" id="ageDisplay">Debe tener al menos 16 años</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="correo" class="form-label">Correo Electrónico *</label>
                            <input type="email" id="correo" name="correo" class="form-control" required>
                            <div class="form-text">Preferiblemente correo institucional (@uncp.edu.pe)</div>
                        </div>

                        <div class="form-group">
                            <label for="contrasena" class="form-label">Contraseña *</label>
                            <input type="password" id="contrasena" name="contrasena" class="form-control" required>
                            <div class="form-text">Mínimo 6 caracteres</div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="foto_perfil" class="form-label">Foto de Perfil</label>
                                    <div class="file-upload">
                                        <input type="file" id="foto_perfil" name="foto_perfil" accept="image/*">
                                        <label for="foto_perfil" class="file-upload-label">
                                            Seleccionar foto (JPG, PNG - máx. 5MB)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="cv_archivo" class="form-label">CV en PDF</label>
                                    <div class="file-upload">
                                        <input type="file" id="cv_archivo" name="cv_archivo" accept=".pdf">
                                        <label for="cv_archivo" class="file-upload-label">
                                            Seleccionar CV (PDF - máx. 10MB)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn" style="width: 100%;">Registrar Estudiante</button>
                    </form>

                <?php else: ?>
                    <!-- Formulario de registro de empresa -->
                    <div class="card-header">
                        <h2 class="card-title">Registro de Empresa</h2>
                    </div>

                    <form method="POST" enctype="multipart/form-data" id="companyRegistrationForm">
                        <input type="hidden" name="user_type" value="company">
                        
                        <div class="row">
                            <div class="col-8">
                                <div class="form-group">
                                    <label for="nombre" class="form-label">Nombre de la Empresa *</label>
                                    <input type="text" id="nombre" name="nombre" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="form-group">
                                    <label for="ruc" class="form-label">RUC *</label>
                                    <input type="text" id="ruc" name="ruc" class="form-control" maxlength="11" required>
                                    <div class="form-text">11 dígitos, debe comenzar con 10 o 20</div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="correo" class="form-label">Correo Electrónico *</label>
                            <input type="email" id="correo" name="correo" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="contrasena" class="form-label">Contraseña *</label>
                            <input type="password" id="contrasena" name="contrasena" class="form-control" required>
                            <div class="form-text">Mínimo 6 caracteres</div>
                        </div>

                        <div class="form-group">
                            <label for="descripcion" class="form-label">Descripción de la Empresa</label>
                            <textarea id="descripcion" name="descripcion" class="form-control" rows="4" 
                                      placeholder="Describe tu empresa, servicios, valores, etc."></textarea>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="logo" class="form-label">Logo de la Empresa</label>
                                    <div class="file-upload">
                                        <input type="file" id="logo" name="logo" accept="image/*">
                                        <label for="logo" class="file-upload-label">
                                            Seleccionar logo (JPG, PNG - máx. 5MB)
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="video_presentacion" class="form-label">Video de Presentación</label>
                                    <div class="file-upload">
                                        <input type="file" id="video_presentacion" name="video_presentacion" accept="video/*">
                                        <label for="video_presentacion" class="file-upload-label">
                                            Seleccionar video (MP4, MOV - máx. 50MB)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn" style="width: 100%;">Registrar Empresa</button>
                    </form>
                <?php endif; ?>

                <div class="text-center mt-3">
                    <p style="color: #6c757d;">
                        ¿Ya tienes cuenta? 
                        <a href="login.php?type=<?php echo $userType; ?>" style="color: #2a5298; text-decoration: none;">
                            Inicia sesión aquí
                        </a>
                    </p>
                    <a href="index.html" style="color: #6c757d; text-decoration: none; font-size: 0.9rem;">
                        ← Volver al inicio
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script src="public/js/main.js"></script>
</body>
</html>
