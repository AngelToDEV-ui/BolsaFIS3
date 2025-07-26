<?php
require_once '../includes/config.php';

// Obtener ID de la empresa desde la URL
$empresaId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$empresaId) {
    header('Location: ../index.html');
    exit;
}

// Obtener información de la empresa
try {
    $sql = "SELECT * FROM Empresas WHERE id_empresa = ? AND activo = 1";
    $stmt = $db->query($sql, [$empresaId]);
    $empresa = $db->fetch($stmt);
    
    if (!$empresa) {
        throw new Exception('Empresa no encontrada');
    }
    
    // Obtener ofertas laborales activas de la empresa
    $sql = "SELECT o.*, COUNT(p.id_postulacion) as total_postulaciones
            FROM OfertasLaborales o
            LEFT JOIN Postulaciones p ON o.id_oferta = p.id_oferta
            WHERE o.id_empresa = ? AND o.activo = 1 
            AND (o.fecha_cierre IS NULL OR o.fecha_cierre > GETDATE())
            GROUP BY o.id_oferta, o.id_empresa, o.titulo, o.descripcion, o.requisitos, 
                     o.salario_min, o.salario_max, o.modalidad, o.ubicacion, 
                     o.fecha_publicacion, o.fecha_cierre, o.activo
            ORDER BY o.fecha_publicacion DESC";
    $stmt = $db->query($sql, [$empresaId]);
    $ofertas = $db->fetchAll($stmt);
    
    // Obtener estadísticas de la empresa
    $sql = "SELECT COUNT(*) as total_ofertas FROM OfertasLaborales WHERE id_empresa = ? AND activo = 1";
    $stmt = $db->query($sql, [$empresaId]);
    $totalOfertas = $db->fetch($stmt)['total_ofertas'];
    
    $sql = "SELECT COUNT(DISTINCT p.id_estudiante) as total_candidatos 
            FROM Postulaciones p 
            INNER JOIN OfertasLaborales o ON p.id_oferta = o.id_oferta 
            WHERE o.id_empresa = ?";
    $stmt = $db->query($sql, [$empresaId]);
    $totalCandidatos = $db->fetch($stmt)['total_candidatos'];
    
} catch (Exception $e) {
    $error = 'Error al cargar información de la empresa: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($empresa) ? htmlspecialchars($empresa['nombre']) : 'Empresa'; ?> - FIS UNCP</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <!-- Debug info: User type: <?php echo isset($_SESSION['user_type']) ? $_SESSION['user_type'] : 'no session'; ?> -->
    <header class="header">
        <div class="header-container">
            <a href="../index.html" class="logo">
                <img src="../public/images/logo-uncp.png" alt="Logo FIS" class="logo-faculty">
                <span class="logo-text"></span>FIS-UNCP</span>
            </a>
            <nav>
                <ul class="nav-menu">
                    <?php if (isset($_SESSION['user_type']) && !empty($_SESSION['user_type'])): ?>
                        <?php if ($_SESSION['user_type'] == 'student'): ?>
                            <li><a href="../student/dashboard.php">Inicio</a></li>
                            <li><a href="../student/dashboard.php">Dashboard</a></li>
                            <li><a href="../student/profile.php">Mi Perfil</a></li>
                        <?php elseif ($_SESSION['user_type'] == 'company'): ?>
                            <li><a href="../company/dashboard.php">Inicio</a></li>
                            <li><a href="../company/dashboard.php">Dashboard</a></li>
                            <li><a href="../company/profile.php">Mi Perfil</a></li>
                        <?php elseif ($_SESSION['user_type'] == 'admin'): ?>
                            <li><a href="../admin/dashboard.php">Inicio</a></li>
                            <li><a href="../admin/dashboard.php">Dashboard</a></li>
                        <?php endif; ?>
                        <li><a href="../logout.php">Cerrar Sesión</a></li>
                    <?php else: ?>
                        <li><a href="../index.html">Inicio</a></li>
                        <li><a href="../login.php">Iniciar Sesión</a></li>
                        <li><a href="../register.php">Registrarse</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php elseif (isset($empresa)): ?>
            
            <!-- Encabezado de la empresa -->
            <div class="card" style="margin-bottom: 2rem;">
                <div style="background: #007bff; color: white; padding: 0.5rem 1rem; margin-bottom: 1rem; border-radius: 5px;">
                    <small>👁️ Vista Pública de Empresa (para estudiantes y visitantes)</small>
                </div>
                <div class="row">
                    <!-- Logo de la empresa -->
                    <div class="col-3">
                        <div class="text-center">
                            <?php if ($empresa['logo'] && file_exists('../uploads/' . $empresa['logo'])): ?>
                                <img src="../uploads/<?php echo $empresa['logo']; ?>" 
                                     alt="Logo de <?php echo htmlspecialchars($empresa['nombre']); ?>" 
                                     class="company-logo">
                            <?php else: ?>
                                <div class="company-logo-placeholder">
                                    <span style="font-size: 3rem; color: #6c757d;">🏢</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Información de la empresa -->
                    <div class="col-9">
                        <div style="padding: 1rem;">
                            <h1 style="color: #1e3c72; margin-bottom: 0.5rem;">
                                <?php echo htmlspecialchars($empresa['nombre']); ?>
                            </h1>
                            <p style="color: #6c757d; margin-bottom: 1rem;">
                                <strong>RUC:</strong> <?php echo $empresa['ruc']; ?> | 
                                <strong>Correo:</strong> <?php echo htmlspecialchars($empresa['correo']); ?>
                            </p>
                            
                            <?php if ($empresa['descripcion']): ?>
                                <div style="margin-bottom: 1rem;">
                                    <h3>Sobre la empresa:</h3>
                                    <p style="text-align: justify;">
                                        <?php echo nl2br(htmlspecialchars($empresa['descripcion'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            
                            <p style="color: #6c757d; font-size: 0.9rem;">
                                <strong>Registrada:</strong> <?php echo formatSqlServerDate($empresa['fecha_registro']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Video de presentación -->
            <?php if ($empresa['video_presentacion'] && file_exists('../uploads/' . $empresa['video_presentacion'])): ?>
                <div class="card" style="margin-bottom: 2rem;">
                    <div class="card-header">
                        <h2 class="card-title">Video de Presentación</h2>
                    </div>
                    <div class="video-container">
                        <video controls style="max-width: 100%; max-height: 400px; border-radius: 8px;">
                            <source src="../uploads/<?php echo $empresa['video_presentacion']; ?>" type="video/mp4">
                            Tu navegador no soporta la reproducción de video.
                        </video>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Estadísticas -->
            <div class="stats-grid" style="margin-bottom: 2rem;">
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalOfertas; ?></div>
                    <div class="stat-label">Ofertas Laborales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo count($ofertas); ?></div>
                    <div class="stat-label">Ofertas Activas</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number"><?php echo $totalCandidatos; ?></div>
                    <div class="stat-label">Candidatos Únicos</div>
                </div>
            </div>

            <!-- Ofertas laborales activas -->
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Ofertas Laborales Activas</h2>
                    <p>Oportunidades disponibles en <?php echo htmlspecialchars($empresa['nombre']); ?></p>
                </div>

                <?php if (!empty($ofertas)): ?>
                    <div class="row">
                        <?php foreach ($ofertas as $oferta): ?>
                            <div class="col-6">
                                <div class="card" style="margin-bottom: 1rem; border-left: 4px solid #1e3c72;">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <h3 style="color: #1e3c72; margin: 0;">
                                            <?php echo htmlspecialchars($oferta['titulo']); ?>
                                        </h3>
                                        <span style="background: #e9ecef; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.8rem;">
                                            <?php echo $oferta['modalidad']; ?>
                                        </span>
                                    </div>
                                    
                                    <?php if ($oferta['ubicacion']): ?>
                                        <p style="color: #6c757d; margin-bottom: 1rem;">
                                            📍 <?php echo htmlspecialchars($oferta['ubicacion']); ?>
                                        </p>
                                    <?php endif; ?>

                                    <p style="color: #6c757d; font-size: 0.9rem; margin-bottom: 1rem;">
                                        <?php echo htmlspecialchars(substr($oferta['descripcion'], 0, 150)) . (strlen($oferta['descripcion']) > 150 ? '...' : ''); ?>
                                    </p>

                                    <?php if ($oferta['salario_min'] && $oferta['salario_max']): ?>
                                        <p style="color: #28a745; font-weight: 500; margin-bottom: 1rem;">
                                            💰 S/<?php echo number_format($oferta['salario_min'], 0); ?> - S/<?php echo number_format($oferta['salario_max'], 0); ?>
                                        </p>
                                    <?php endif; ?>

                                    <div style="font-size: 0.8rem; color: #6c757d; margin-bottom: 1rem;">
                                        <p>📅 Publicado: <?php echo formatSqlServerDate($oferta['fecha_publicacion']); ?></p>
                                        <?php if ($oferta['fecha_cierre']): ?>
                                            <p>⏰ Cierra: <?php echo formatSqlServerDate($oferta['fecha_cierre']); ?></p>
                                        <?php endif; ?>
                                        <p>👥 Postulaciones: <?php echo $oferta['total_postulaciones']; ?></p>
                                    </div>

                                    <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] == 'student'): ?>
                                        <!-- Verificar si ya postuló -->
                                        <?php
                                        $yaPostulado = false;
                                        if (isset($_SESSION['user_id'])) {
                                            try {
                                                $checkSql = "SELECT COUNT(*) as count FROM Postulaciones WHERE id_estudiante = ? AND id_oferta = ?";
                                                $checkStmt = $db->query($checkSql, [$_SESSION['user_id'], $oferta['id_oferta']]);
                                                $yaPostulado = $db->fetch($checkStmt)['count'] > 0;
                                            } catch (Exception $e) {
                                                // Ignorar error
                                            }
                                        }
                                        ?>

                                        <?php if ($yaPostulado): ?>
                                            <button class="btn btn-secondary" disabled style="width: 100%;">
                                                ✓ Ya Postulado
                                            </button>
                                        <?php else: ?>
                                            <form method="POST" action="../student/dashboard.php" style="margin: 0;">
                                                <input type="hidden" name="action" value="postular">
                                                <input type="hidden" name="id_oferta" value="<?php echo $oferta['id_oferta']; ?>">
                                                <button type="submit" class="btn" style="width: 100%;">
                                                    Postular Ahora
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    <?php elseif (!isset($_SESSION['user_type'])): ?>
                                        <a href="../login.php" class="btn" style="width: 100%; display: block; text-align: center;">
                                            Iniciar Sesión para Postular
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center">
                        <p>Esta empresa no tiene ofertas laborales activas en este momento.</p>
                        <p style="color: #6c757d;">Revisa más tarde para ver nuevas oportunidades.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Facultad de Ingeniería de Sistemas - UNCP</p>
    </footer>

    <script src="../public/js/main.js"></script>
</body>
</html>
