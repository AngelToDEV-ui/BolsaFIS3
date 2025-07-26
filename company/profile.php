<?php
require_once '../includes/config.php';
requireUserType(['company']);

$companyId = $_SESSION['user_id'];

// Obtener información de la empresa
try {
    $sql = "SELECT * FROM Empresas WHERE id_empresa = ?";
    $stmt = $db->query($sql, [$companyId]);
    $company = $db->fetch($stmt);
    
    if (!$company) {
        throw new Exception('Empresa no encontrada');
    }
    
} catch (Exception $e) {
    $error = 'Error al cargar datos del perfil: ' . $e->getMessage();
}

// Manejar actualización de perfil
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $nombre = sanitizeInput($_POST['nombre']);
        $correo = sanitizeInput($_POST['correo']);
        $descripcion = sanitizeInput($_POST['descripcion']);
        
        // Validaciones
        if (!validateEmail($correo)) {
            throw new Exception('Correo inválido');
        }
        
        // Manejar archivos opcionales
        $logo = $company['logo'];
        $video_presentacion = $company['video_presentacion'];
        
        if (isset($_FILES['logo']) && $_FILES['logo']['size'] > 0) {
            $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
            $logo = uploadFile($_FILES['logo'], $allowedTypes, 'logos');
        }
        
        if (isset($_FILES['video_presentacion']) && $_FILES['video_presentacion']['size'] > 0) {
            $allowedTypes = ['video/mp4', 'video/mpeg', 'video/quicktime'];
            $video_presentacion = uploadFile($_FILES['video_presentacion'], $allowedTypes, 'videos');
        }
        
        // Actualizar perfil
        $sql = "UPDATE Empresas SET nombre = ?, correo = ?, descripcion = ?, 
                logo = ?, video_presentacion = ? WHERE id_empresa = ?";
        $db->execute($sql, [$nombre, $correo, $descripcion, $logo, $video_presentacion, $companyId]);
        
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
    <title>Mi Perfil - Empresa FIS UNCP</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="dashboard.php" class="logo">
                <img src="../public/images/logo-uncp.png" alt="Logo FIS" class="logo-faculty">
                <span class="logo-text">FIS-UNCP Empresa</span>
            </a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="applications.php">Postulaciones</a></li>
                    <li><a href="profile.php">Mi Perfil</a></li>
                    <li><a href="../logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Mi Perfil de Empresa</h1>
                <p>Administra la información de tu empresa y archivos multimedia</p>
                <small style="background: #28a745; color: white; padding: 0.25rem 0.5rem; border-radius: 3px;">
                    ✓ Perfil de Empresa - Modo Edición
                </small>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (isset($company)): ?>
                <div class="row" style="margin-bottom: 2rem;">
                    <!-- Logo de la empresa -->
                    <div class="col-4">
                        <div class="text-center">
                            <?php if ($company['logo'] && file_exists('../uploads/' . $company['logo'])): ?>
                                <img src="../uploads/<?php echo $company['logo']; ?>" 
                                     alt="Logo de la empresa" 
                                     class="company-logo">
                            <?php else: ?>
                                <div class="company-logo-placeholder">
                                    <span style="font-size: 3rem; color: #6c757d;">🏢</span>
                                </div>
                            <?php endif; ?>
                            <h3 style="margin-top: 1rem; color: #1e3c72;">
                                <?php echo htmlspecialchars($company['nombre']); ?>
                            </h3>
                            <p style="color: #6c757d;"><?php echo htmlspecialchars($company['correo']); ?></p>
                        </div>
                    </div>
                    
                    <!-- Información básica -->
                    <div class="col-8">
                        <div class="card" style="height: 100%;">
                            <div class="card-header">
                                <h3>Información de la Empresa</h3>
                            </div>
                            <div style="padding: 1rem;">
                                <p><strong>RUC:</strong> <?php echo $company['ruc']; ?></p>
                                <p><strong>Fecha de Registro:</strong> <?php echo formatSqlServerDate($company['fecha_registro']); ?></p>
                                <?php if ($company['video_presentacion'] && file_exists('../uploads/' . $company['video_presentacion'])): ?>
                                    <p><strong>Video de Presentación:</strong> 
                                        <a href="../uploads/<?php echo $company['video_presentacion']; ?>" target="_blank" class="btn btn-sm">
                                            🎥 Ver Video
                                        </a>
                                    </p>
                                <?php endif; ?>
                                <?php if ($company['descripcion']): ?>
                                    <p><strong>Descripción:</strong></p>
                                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin-top: 0.5rem;">
                                        <?php echo nl2br(htmlspecialchars($company['descripcion'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <form method="POST" enctype="multipart/form-data">
                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="nombre">Nombre de la Empresa *</label>
                                <input type="text" id="nombre" name="nombre" 
                                       value="<?php echo htmlspecialchars($company['nombre']); ?>" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="correo">Correo Electrónico *</label>
                                <input type="email" id="correo" name="correo" 
                                       value="<?php echo htmlspecialchars($company['correo']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="ruc">RUC</label>
                                <input type="text" id="ruc" name="ruc" 
                                       value="<?php echo $company['ruc']; ?>" readonly>
                                <small class="form-text">El RUC no puede ser modificado</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="fecha_registro">Fecha de Registro</label>
                                <input type="text" id="fecha_registro" name="fecha_registro" 
                                       value="<?php echo formatSqlServerDate($company['fecha_registro']); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="descripcion">Descripción de la Empresa</label>
                        <textarea id="descripcion" name="descripcion" class="form-control" rows="4" 
                                  placeholder="Describe tu empresa, su misión, visión y servicios..."><?php echo htmlspecialchars($company['descripcion']); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <div class="form-group">
                                <label for="logo">Logo de la Empresa</label>
                                <input type="file" id="logo" name="logo" accept=".jpg,.jpeg,.png">
                                <?php if ($company['logo']): ?>
                                    <div class="file-preview">
                                        Archivo actual: <?php echo basename($company['logo']); ?>
                                        <br><a href="../uploads/<?php echo $company['logo']; ?>" target="_blank">Ver logo actual</a>
                                    </div>
                                <?php endif; ?>
                                <small class="form-text">Formatos permitidos: JPG, JPEG, PNG. Tamaño máximo: 5MB</small>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-group">
                                <label for="video_presentacion">Video de Presentación</label>
                                <input type="file" id="video_presentacion" name="video_presentacion" accept=".mp4,.mpeg,.mov">
                                <?php if ($company['video_presentacion']): ?>
                                    <div class="file-preview">
                                        Archivo actual: <?php echo basename($company['video_presentacion']); ?>
                                        <br><a href="../uploads/<?php echo $company['video_presentacion']; ?>" target="_blank">Ver video actual</a>
                                    </div>
                                <?php endif; ?>
                                <small class="form-text">Formatos: MP4, MPEG, MOV. Tamaño máximo: 50MB</small>
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
                        <h3>Estadísticas de mi Empresa</h3>
                    </div>
                    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
                        <div class="stat-card">
                            <div class="stat-number">
                                <?php 
                                try {
                                    $sql = "SELECT COUNT(*) as total FROM OfertasLaborales WHERE id_empresa = ? AND activo = 1";
                                    $stmt = $db->query($sql, [$companyId]);
                                    echo $db->fetch($stmt)['total'];
                                } catch (Exception $e) {
                                    echo '0';
                                }
                                ?>
                            </div>
                            <div class="stat-label">Ofertas Activas</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">
                                <?php 
                                try {
                                    $sql = "SELECT COUNT(p.id_postulacion) as total
                                            FROM OfertasLaborales o
                                            INNER JOIN Postulaciones p ON o.id_oferta = p.id_oferta
                                            WHERE o.id_empresa = ?";
                                    $stmt = $db->query($sql, [$companyId]);
                                    echo $db->fetch($stmt)['total'];
                                } catch (Exception $e) {
                                    echo '0';
                                }
                                ?>
                            </div>
                            <div class="stat-label">Total Postulaciones</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">
                                <?php 
                                try {
                                    $sql = "SELECT COUNT(p.id_postulacion) as total
                                            FROM OfertasLaborales o
                                            INNER JOIN Postulaciones p ON o.id_oferta = p.id_oferta
                                            WHERE o.id_empresa = ? AND p.estado = 'Pendiente'";
                                    $stmt = $db->query($sql, [$companyId]);
                                    echo $db->fetch($stmt)['total'];
                                } catch (Exception $e) {
                                    echo '0';
                                }
                                ?>
                            </div>
                            <div class="stat-label">Pendientes</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number">
                                <?php 
                                try {
                                    $sql = "SELECT COUNT(DISTINCT p.id_estudiante) as total
                                            FROM OfertasLaborales o
                                            INNER JOIN Postulaciones p ON o.id_oferta = p.id_oferta
                                            WHERE o.id_empresa = ?";
                                    $stmt = $db->query($sql, [$companyId]);
                                    echo $db->fetch($stmt)['total'];
                                } catch (Exception $e) {
                                    echo '0';
                                }
                                ?>
                            </div>
                            <div class="stat-label">Candidatos Únicos</div>
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
