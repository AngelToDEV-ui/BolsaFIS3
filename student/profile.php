<?php
require_once '../includes/config.php';
requireUserType(['student']);

$studentId = $_SESSION['user_id'];

// Obtener información del estudiante
try {
    $sql = "SELECT * FROM Estudiantes WHERE id_estudiante = ?";
    $stmt = $db->query($sql, [$studentId]);
    $student = $db->fetch($stmt);
    
    if (!$student) {
        throw new Exception('Estudiante no encontrado');
    }
    
} catch (Exception $e) {
    $error = 'Error al cargar datos del perfil: ' . $e->getMessage();
}

// Manejar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nombres = sanitizeInput($_POST['nombres']);
        $apellidos = sanitizeInput($_POST['apellidos']);
        $correo = sanitizeInput($_POST['correo']);
        
        // Validaciones
        if (!validateEmail($correo)) {
            throw new Exception('Correo inválido');
        }
        
        // Manejar archivos opcionales
        $foto_perfil = $student['foto_perfil'];
        $cv_archivo = $student['cv_archivo'];
        
        if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['size'] > 0) {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            $foto_perfil = uploadFile($_FILES['foto_perfil'], $allowedTypes, 'photos');
        }
        
        if (isset($_FILES['cv_archivo']) && $_FILES['cv_archivo']['size'] > 0) {
            $allowedTypes = ['application/pdf'];
            $cv_archivo = uploadFile($_FILES['cv_archivo'], $allowedTypes, 'cvs');
        }
        
        // Actualizar perfil
        $sql = "UPDATE Estudiantes SET nombres = ?, apellidos = ?, correo = ?, 
                foto_perfil = ?, cv_archivo = ? WHERE id_estudiante = ?";
        $db->execute($sql, [$nombres, $apellidos, $correo, $foto_perfil, $cv_archivo, $studentId]);
        
        showMessage('Perfil actualizado exitosamente', 'success');
        header('Location: profile.php');
        exit;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

$message = getAndClearMessage();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Perfil - FIS UNCP</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="dashboard.php" class="logo">
                <img src="../public/images/logo-uncp.png" alt="Logo FIS" class="logo-faculty">
                <span class="logo-text">FIS-UNCP Estudiante</span>
            </a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="dashboard.php">Ofertas</a></li>
                    <li><a href="applications.php">Mis Postulaciones</a></li>
                    <li><a href="profile.php">Mi Perfil</a></li>
                    <li><a href="../logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Mi Perfil</h1>
                <p>Administra tu información personal y documentos</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (isset($student)): ?>
                <div class="row" style="margin-bottom: 2rem;">
                    <!-- Foto de perfil -->
                    <div class="col-4">
                        <div class="text-center">
                            <?php if ($student['foto_perfil'] && file_exists('../uploads/' . $student['foto_perfil'])): ?>
                                <img src="../uploads/<?php echo $student['foto_perfil']; ?>" 
                                     alt="Foto de perfil" 
                                     class="profile-image">
                            <?php else: ?>
                                <div class="profile-placeholder">
                                    <span style="font-size: 3rem; color: #6c757d;">👤</span>
                                </div>
                            <?php endif; ?>
                            <h3 style="margin-top: 1rem; color: #1e3c72;">
                                <?php echo htmlspecialchars($student['nombres'] . ' ' . $student['apellidos']); ?>
                            </h3>
                            <p style="color: #6c757d;"><?php echo htmlspecialchars($student['correo']); ?></p>
                        </div>
                    </div>
                    
                    <!-- Información básica -->
                    <div class="col-8">
                        <div class="card" style="height: 100%;">
                            <div class="card-header">
                                <h3>Información Personal</h3>
                            </div>
                            <div style="padding: 1rem;">
                                <p><strong>DNI:</strong> <?php echo $student['dni']; ?></p>
                                <p><strong>Edad:</strong> <?php echo calculateAge($student['anio_nacimiento']); ?> años</p>
                                <p><strong>Año de Nacimiento:</strong> <?php echo $student['anio_nacimiento']; ?></p>
                                <p><strong>Fecha de Registro:</strong> <?php echo formatSqlServerDate($student['fecha_registro']); ?></p>
                                <?php if ($student['cv_archivo'] && file_exists('../uploads/' . $student['cv_archivo'])): ?>
                                    <p><strong>CV:</strong> 
                                        <a href="../uploads/<?php echo $student['cv_archivo']; ?>" target="_blank" class="btn btn-sm">
                                            📄 Ver CV
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="nombres">Nombres *</label>
                                <input type="text" id="nombres" name="nombres" 
                                       value="<?php echo htmlspecialchars($student['nombres']); ?>" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="apellidos">Apellidos *</label>
                                <input type="text" id="apellidos" name="apellidos" 
                                       value="<?php echo htmlspecialchars($student['apellidos']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="dni">DNI</label>
                                <input type="text" id="dni" name="dni" 
                                       value="<?php echo $student['dni']; ?>" readonly>
                                <small class="form-text">El DNI no puede ser modificado</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="correo">Correo Electrónico *</label>
                                <input type="email" id="correo" name="correo" 
                                       value="<?php echo htmlspecialchars($student['correo']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="anio_nacimiento">Año de Nacimiento</label>
                                <input type="number" id="anio_nacimiento" name="anio_nacimiento" 
                                       value="<?php echo $student['anio_nacimiento']; ?>" readonly>
                                <small class="form-text">Edad: <?php echo calculateAge($student['anio_nacimiento']); ?> años</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="fecha_registro">Fecha de Registro</label>
                                <input type="text" id="fecha_registro" name="fecha_registro" 
                                       value="<?php echo formatSqlServerDate($student['fecha_registro']); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">                        <div class="form-group">
                            <label for="foto_perfil">Foto de Perfil</label>
                            <input type="file" id="foto_perfil" name="foto_perfil" accept=".jpg,.jpeg,.png">
                            <?php if ($student['foto_perfil']): ?>
                                <small class="form-text">
                                    Archivo actual: <?php echo basename($student['foto_perfil']); ?>
                                    <br><a href="../uploads/<?php echo $student['foto_perfil']; ?>" target="_blank">Ver archivo actual</a>
                                </small>
                            <?php endif; ?>
                            <small class="form-text">Formatos permitidos: JPG, JPEG, PNG. Tamaño máximo: 5MB</small>
                        </div>
                        </div>
                        <div class="col-6">                        <div class="form-group">
                            <label for="cv_archivo">Curriculum Vitae (PDF)</label>
                            <input type="file" id="cv_archivo" name="cv_archivo" accept=".pdf">
                            <?php if ($student['cv_archivo']): ?>
                                <small class="form-text">
                                    Archivo actual: <?php echo basename($student['cv_archivo']); ?>
                                    <br><a href="../uploads/<?php echo $student['cv_archivo']; ?>" target="_blank">Ver CV actual</a>
                                </small>
                            <?php endif; ?>
                            <small class="form-text">Solo archivos PDF. Tamaño máximo: 10MB</small>
                        </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn">Actualizar Perfil</button>
                        <a href="dashboard.php" class="btn btn-secondary">Cancelar</a>
                    </div>
                </form>

                <!-- Información adicional -->
                <div class="card" style="margin-top: 2rem;">
                    <div class="card-header">
                        <h3>Estadísticas de mi Perfil</h3>
                    </div>
                    <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
                        <div class="stat-card">
                            <div class="stat-number">
                                <?php 
                                try {
                                    $sql = "SELECT COUNT(*) as total FROM Postulaciones WHERE id_estudiante = ?";
                                    $stmt = $db->query($sql, [$studentId]);
                                    echo $db->fetch($stmt)['total'];
                                } catch (Exception $e) {
                                    echo '0';
                                }
                                ?>
                            </div>
                            <div class="stat-label">Postulaciones Realizadas</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">
                                <?php 
                                try {
                                    $sql = "SELECT COUNT(*) as total FROM Postulaciones WHERE id_estudiante = ? AND estado = 'Seleccionado'";
                                    $stmt = $db->query($sql, [$studentId]);
                                    echo $db->fetch($stmt)['total'];
                                } catch (Exception $e) {
                                    echo '0';
                                }
                                ?>
                            </div>
                            <div class="stat-label">Seleccionado</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">
                                <?php 
                                try {
                                    $sql = "SELECT COUNT(*) as total FROM Postulaciones WHERE id_estudiante = ? AND estado = 'Pendiente'";
                                    $stmt = $db->query($sql, [$studentId]);
                                    echo $db->fetch($stmt)['total'];
                                } catch (Exception $e) {
                                    echo '0';
                                }
                                ?>
                            </div>
                            <div class="stat-label">En Proceso</div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Facultad de Ingeniería de Sistemas - UNCP</p>
    </footer>

    <script src="../public/js/main.js"></script>
</body>
</html>
