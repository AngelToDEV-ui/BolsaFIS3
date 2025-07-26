<?php
require_once '../includes/config.php';
requireUserType(['student']);

$studentId = $_SESSION['user_id'];

// Obtener información del estudiante
try {
    $sql = "SELECT * FROM Estudiantes WHERE id_estudiante = ?";
    $stmt = $db->query($sql, [$studentId]);
    $student = $db->fetch($stmt);
    
    // Obtener estadísticas del estudiante
    $sql = "SELECT COUNT(*) as total FROM Postulaciones WHERE id_estudiante = ?";
    $stmt = $db->query($sql, [$studentId]);
    $totalPostulaciones = $db->fetch($stmt)['total'];
    
    $sql = "SELECT COUNT(*) as total FROM Postulaciones WHERE id_estudiante = ? AND estado = 'Seleccionado'";
    $stmt = $db->query($sql, [$studentId]);
    $seleccionado = $db->fetch($stmt)['total'];
    
    $sql = "SELECT COUNT(*) as total FROM Postulaciones WHERE id_estudiante = ? AND estado = 'Pendiente'";
    $stmt = $db->query($sql, [$studentId]);
    $pendientes = $db->fetch($stmt)['total'];
    
    // Obtener ofertas laborales activas
    $sql = "SELECT o.*, e.nombre as empresa_nombre, e.logo,
                   CASE WHEN p.id_postulacion IS NOT NULL THEN 1 ELSE 0 END as ya_postulado
            FROM OfertasLaborales o
            INNER JOIN Empresas e ON o.id_empresa = e.id_empresa
            LEFT JOIN Postulaciones p ON o.id_oferta = p.id_oferta AND p.id_estudiante = ?
            WHERE o.activo = 1 AND (o.fecha_cierre IS NULL OR o.fecha_cierre > GETDATE())
            ORDER BY o.fecha_publicacion DESC";
    $stmt = $db->query($sql, [$studentId]);
    $ofertas = $db->fetchAll($stmt);
    
} catch (Exception $e) {
    $error = 'Error al cargar datos: ' . $e->getMessage();
}

// Manejar postulación
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'postular') {
    try {
        $idOferta = intval($_POST['id_oferta']);
        $sql = "EXEC sp_PostularOferta ?, ?";
        $db->execute($sql, [$studentId, $idOferta]);
        showMessage('Postulación enviada exitosamente', 'success');
        header('Location: dashboard.php');
        exit;
    } catch (Exception $e) {
        $error = 'Error al postular: ' . $e->getMessage();
    }
}

$message = getAndClearMessage();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Estudiante - FIS UNCP</title>
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
        <div class="dashboard-header">
            <h1>Bienvenido, <?php echo htmlspecialchars($student['nombres'] . ' ' . $student['apellidos']); ?></h1>
            <p>Encuentra las mejores oportunidades laborales para estudiantes de Ingeniería de Sistemas</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message['type']; ?>">
                <?php echo $message['message']; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Estadísticas del estudiante -->
        <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalPostulaciones; ?></div>
                <div class="stat-label">Total Postulaciones</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $pendientes; ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $seleccionado; ?></div>
                <div class="stat-label">Seleccionado</div>
            </div>
        </div>

        <!-- Filtros de ofertas -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Filtrar Ofertas</h2>
            </div>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <button onclick="StudentDashboard.filterOffers('all')" class="btn btn-sm">Todas</button>
                <button onclick="StudentDashboard.filterOffers('Presencial')" class="btn btn-sm btn-secondary">Presencial</button>
                <button onclick="StudentDashboard.filterOffers('Remoto')" class="btn btn-sm btn-secondary">Remoto</button>
                <button onclick="StudentDashboard.filterOffers('Híbrido')" class="btn btn-sm btn-secondary">Híbrido</button>
            </div>
        </div>

        <!-- Ofertas laborales -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Ofertas Laborales Disponibles</h2>
                <p>Total de ofertas: <?php echo count($ofertas); ?></p>
            </div>

            <?php if (!empty($ofertas)): ?>
                <div class="row">
                    <?php foreach ($ofertas as $oferta): ?>
                        <div class="col-6">
                            <div class="card offer-card" style="margin-bottom: 1rem;">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h3 style="color: #1e3c72; margin: 0;">
                                        <?php echo htmlspecialchars($oferta['titulo']); ?>
                                    </h3>
                                    <span class="offer-modality" style="background: #e9ecef; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.8rem;">
                                        <?php echo $oferta['modalidad']; ?>
                                    </span>
                                </div>
                                
                                <div style="margin-bottom: 1rem;">
                                    <strong>
                                        <a href="../company/public_profile.php?id=<?php echo $oferta['id_empresa']; ?>" 
                                           class="company-link">
                                            <?php echo htmlspecialchars($oferta['empresa_nombre']); ?>
                                        </a>
                                    </strong>
                                    <?php if ($oferta['ubicacion']): ?>
                                        <br><small style="color: #6c757d;">📍 <?php echo htmlspecialchars($oferta['ubicacion']); ?></small>
                                    <?php endif; ?>
                                </div>

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
                                </div>

                                <?php if ($oferta['ya_postulado']): ?>
                                    <button class="btn btn-secondary" disabled style="width: 100%;">
                                        ✓ Ya Postulado
                                    </button>
                                <?php else: ?>
                                    <form method="POST" style="margin: 0;">
                                        <input type="hidden" name="action" value="postular">
                                        <input type="hidden" name="id_oferta" value="<?php echo $oferta['id_oferta']; ?>">
                                        <button type="submit" class="btn" style="width: 100%;">
                                            Postular Ahora
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <p>No hay ofertas laborales disponibles en este momento.</p>
                    <p style="color: #6c757d;">Revisa más tarde para ver nuevas oportunidades.</p>
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
