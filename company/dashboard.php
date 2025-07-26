<?php
require_once '../includes/config.php';
requireUserType(['company']);

$companyId = $_SESSION['user_id'];

// Obtener información de la empresa
try {
    $sql = "SELECT * FROM Empresas WHERE id_empresa = ?";
    $stmt = $db->query($sql, [$companyId]);
    $company = $db->fetch($stmt);
    
    // Obtener estadísticas de la empresa
    $sql = "SELECT COUNT(*) as total FROM OfertasLaborales WHERE id_empresa = ? AND activo = 1";
    $stmt = $db->query($sql, [$companyId]);
    $totalOfertas = $db->fetch($stmt)['total'];
    
    $sql = "SELECT COUNT(p.id_postulacion) as total
            FROM OfertasLaborales o
            INNER JOIN Postulaciones p ON o.id_oferta = p.id_oferta
            WHERE o.id_empresa = ?";
    $stmt = $db->query($sql, [$companyId]);
    $totalPostulaciones = $db->fetch($stmt)['total'];
    
    $sql = "SELECT COUNT(p.id_postulacion) as total
            FROM OfertasLaborales o
            INNER JOIN Postulaciones p ON o.id_oferta = p.id_oferta
            WHERE o.id_empresa = ? AND p.estado = 'Pendiente'";
    $stmt = $db->query($sql, [$companyId]);
    $pendientes = $db->fetch($stmt)['total'];
    
    // Obtener ofertas de la empresa
    $sql = "SELECT o.*, COUNT(p.id_postulacion) as total_postulaciones
            FROM OfertasLaborales o
            LEFT JOIN Postulaciones p ON o.id_oferta = p.id_oferta
            WHERE o.id_empresa = ? AND o.activo = 1
            GROUP BY o.id_oferta, o.id_empresa, o.titulo, o.descripcion, o.requisitos, 
                     o.salario_min, o.salario_max, o.modalidad, o.ubicacion, 
                     o.fecha_publicacion, o.fecha_cierre, o.activo
            ORDER BY o.fecha_publicacion DESC";
    $stmt = $db->query($sql, [$companyId]);
    $ofertas = $db->fetchAll($stmt);
    
} catch (Exception $e) {
    $error = 'Error al cargar datos: ' . $e->getMessage();
}

// Manejar creación de nueva oferta
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'create_offer') {
    try {
        $titulo = sanitizeInput($_POST['titulo']);
        $descripcion = sanitizeInput($_POST['descripcion']);
        $requisitos = sanitizeInput($_POST['requisitos']);
        $salario_min = $_POST['salario_min'] ? floatval($_POST['salario_min']) : null;
        $salario_max = $_POST['salario_max'] ? floatval($_POST['salario_max']) : null;
        $modalidad = sanitizeInput($_POST['modalidad']);
        $ubicacion = sanitizeInput($_POST['ubicacion']);
        $fecha_cierre = $_POST['fecha_cierre'] ? $_POST['fecha_cierre'] : null;
        
        $sql = "EXEC sp_CrearOfertaLaboral ?, ?, ?, ?, ?, ?, ?, ?, ?";
        $params = [$companyId, $titulo, $descripcion, $requisitos, $salario_min, $salario_max, $modalidad, $ubicacion, $fecha_cierre];
        
        $db->execute($sql, $params);
        showMessage('Oferta laboral creada exitosamente', 'success');
        header('Location: dashboard.php');
        exit;
    } catch (Exception $e) {
        $error = 'Error al crear oferta: ' . $e->getMessage();
    }
}

$message = getAndClearMessage();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Empresa - FIS UNCP</title>
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
        <div class="dashboard-header">
            <h1>Panel de <?php echo htmlspecialchars($company['nombre']); ?></h1>
            <p>Gestiona tus ofertas laborales y encuentra el mejor talento de FIS-UNCP</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message['type']; ?>">
                <?php echo $message['message']; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Estadísticas de la empresa -->
        <div class="stats-grid" style="grid-template-columns: repeat(3, 1fr);">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalOfertas; ?></div>
                <div class="stat-label">Ofertas Publicadas</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalPostulaciones; ?></div>
                <div class="stat-label">Total Postulaciones</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $pendientes; ?></div>
                <div class="stat-label">Pendientes de Revisar</div>
            </div>
        </div>

        <div class="row">
            <!-- Formulario para nueva oferta -->
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Publicar Nueva Oferta</h2>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="action" value="create_offer">
                        
                        <div class="form-group">
                            <label for="titulo" class="form-label">Título del Puesto *</label>
                            <input type="text" id="titulo" name="titulo" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label for="descripcion" class="form-label">Descripción *</label>
                            <textarea id="descripcion" name="descripcion" class="form-control" rows="4" required></textarea>
                        </div>

                        <div class="form-group">
                            <label for="requisitos" class="form-label">Requisitos</label>
                            <textarea id="requisitos" name="requisitos" class="form-control" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="salario_min" class="form-label">Salario Mínimo</label>
                                    <input type="number" id="salario_min" name="salario_min" class="form-control" step="0.01">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="salario_max" class="form-label">Salario Máximo</label>
                                    <input type="number" id="salario_max" name="salario_max" class="form-control" step="0.01">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="modalidad" class="form-label">Modalidad *</label>
                                    <select id="modalidad" name="modalidad" class="form-control" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="Presencial">Presencial</option>
                                        <option value="Remoto">Remoto</option>
                                        <option value="Híbrido">Híbrido</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="ubicacion" class="form-label">Ubicación</label>
                                    <input type="text" id="ubicacion" name="ubicacion" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="fecha_cierre" class="form-label">Fecha de Cierre (Opcional)</label>
                            <input type="date" id="fecha_cierre" name="fecha_cierre" class="form-control" 
                                   min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>">
                        </div>

                        <button type="submit" class="btn" style="width: 100%;">Publicar Oferta</button>
                    </form>
                </div>
            </div>

            <!-- Lista de ofertas actuales -->
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Mis Ofertas Publicadas</h2>
                    </div>

                    <?php if (!empty($ofertas)): ?>
                        <div style="max-height: 600px; overflow-y: auto;">
                            <?php foreach ($ofertas as $oferta): ?>
                                <div class="card" style="margin-bottom: 1rem; border: 1px solid #dee2e6;">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <h4 style="color: #1e3c72; margin: 0;">
                                            <?php echo htmlspecialchars($oferta['titulo']); ?>
                                        </h4>
                                        <span style="background: #e9ecef; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.8rem;">
                                            <?php echo $oferta['modalidad']; ?>
                                        </span>
                                    </div>
                                    
                                    <p style="color: #6c757d; font-size: 0.9rem; margin-bottom: 1rem;">
                                        <?php echo htmlspecialchars(substr($oferta['descripcion'], 0, 100)) . (strlen($oferta['descripcion']) > 100 ? '...' : ''); ?>
                                    </p>

                                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 0.8rem;">
                                        <div>
                                            <span style="color: #28a745; font-weight: 500;">
                                                📊 <?php echo $oferta['total_postulaciones']; ?> postulaciones
                                            </span>
                                            <br>
                                            <span style="color: #6c757d;">
                                                📅 <?php echo formatSqlServerDate($oferta['fecha_publicacion'], 'd/m/Y'); ?>
                                            </span>
                                        </div>
                                        <a href="applications.php?oferta=<?php echo $oferta['id_oferta']; ?>" class="btn btn-sm">
                                            Ver Postulaciones
                                        </a>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center">
                            <p>Aún no has publicado ninguna oferta laboral.</p>
                            <p style="color: #6c757d;">Usa el formulario de la izquierda para crear tu primera oferta.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Facultad de Ingeniería de Sistemas - UNCP</p>
    </footer>

    <script src="../public/js/main.js"></script>
</body>
</html>
