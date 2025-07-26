<?php
require_once '../includes/config.php';
requireUserType(['company']);

$companyId = $_SESSION['user_id'];
$selectedOferta = $_GET['oferta'] ?? null;

// Obtener postulaciones
try {
    $sql = "SELECT p.*, o.titulo, o.descripcion as oferta_descripcion,
                   e.nombres, e.apellidos, e.dni, e.correo, e.anio_nacimiento,
                   e.foto_perfil, e.cv_archivo,
                   dbo.CalcularEdad(e.anio_nacimiento) as edad
            FROM Postulaciones p
            INNER JOIN OfertasLaborales o ON p.id_oferta = o.id_oferta
            INNER JOIN Estudiantes e ON p.id_estudiante = e.id_estudiante
            WHERE o.id_empresa = ?";
    
    $params = [$companyId];
    
    if ($selectedOferta) {
        $sql .= " AND p.id_oferta = ?";
        $params[] = $selectedOferta;
    }
    
    $sql .= " ORDER BY p.fecha_postulacion DESC";
    
    $stmt = $db->query($sql, $params);
    $postulaciones = $db->fetchAll($stmt);
    
    // Obtener ofertas para el filtro
    $sql = "SELECT id_oferta, titulo FROM OfertasLaborales WHERE id_empresa = ? AND activo = 1 ORDER BY fecha_publicacion DESC";
    $stmt = $db->query($sql, [$companyId]);
    $ofertas = $db->fetchAll($stmt);
    
} catch (Exception $e) {
    $error = 'Error al cargar postulaciones: ' . $e->getMessage();
}

// Manejar cambio de estado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $postulacionId = intval($_POST['postulacion_id']);
        $nuevoEstado = sanitizeInput($_POST['nuevo_estado']);
        $comentarios = sanitizeInput($_POST['comentarios']);
        
        $sql = "UPDATE Postulaciones SET estado = ?, comentarios = ? WHERE id_postulacion = ?";
        $db->execute($sql, [$nuevoEstado, $comentarios, $postulacionId]);
        
        showMessage('Estado de postulación actualizado exitosamente', 'success');
        header('Location: applications.php' . ($selectedOferta ? '?oferta=' . $selectedOferta : ''));
        exit;
    } catch (Exception $e) {
        $error = 'Error al actualizar estado: ' . $e->getMessage();
    }
}

$message = getAndClearMessage();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postulaciones - Empresa FIS UNCP</title>
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
                <h1 class="card-title">Gestión de Postulaciones</h1>
                <p>Revisa y gestiona las postulaciones a tus ofertas laborales</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Filtros -->
            <div class="mb-3">
                <div style="display: flex; gap: 1rem; align-items: center; flex-wrap: wrap;">
                    <div>
                        <label for="filterOferta" style="margin-right: 0.5rem;">Filtrar por oferta:</label>
                        <select id="filterOferta" onchange="window.location.href='applications.php' + (this.value ? '?oferta=' + this.value : '')" style="padding: 0.5rem;">
                            <option value="">Todas las ofertas</option>
                            <?php foreach ($ofertas as $oferta): ?>
                                <option value="<?php echo $oferta['id_oferta']; ?>" <?php echo $selectedOferta == $oferta['id_oferta'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($oferta['titulo']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <button onclick="CompanyDashboard.filterApplications('all')" class="btn btn-sm">Todas</button>
                        <button onclick="CompanyDashboard.filterApplications('Pendiente')" class="btn btn-sm btn-secondary">Pendientes</button>
                        <button onclick="CompanyDashboard.filterApplications('Revisado')" class="btn btn-sm btn-secondary">Revisadas</button>
                        <button onclick="CompanyDashboard.filterApplications('Seleccionado')" class="btn btn-sm" style="background: #28a745;">Seleccionadas</button>
                        <button onclick="CompanyDashboard.filterApplications('Rechazado')" class="btn btn-sm btn-danger">Rechazadas</button>
                    </div>
                </div>
            </div>

            <?php if (!empty($postulaciones)): ?>
                <div class="mb-3">
                    <p>Total de postulaciones: <strong><?php echo count($postulaciones); ?></strong></p>
                </div>

                <div class="applications-table">
                    <?php foreach ($postulaciones as $postulacion): ?>
                        <div class="card" style="margin-bottom: 1.5rem; border-left: 4px solid <?php 
                            echo $postulacion['estado'] == 'Seleccionado' ? '#28a745' : 
                                ($postulacion['estado'] == 'Rechazado' ? '#dc3545' : 
                                ($postulacion['estado'] == 'Revisado' ? '#007bff' : '#ffc107')); ?>;">
                            
                            <div class="row">
                                <div class="col-8">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div>
                                            <h3 style="color: #1e3c72; margin: 0 0 0.5rem 0;">
                                                <?php echo htmlspecialchars($postulacion['nombres'] . ' ' . $postulacion['apellidos']); ?>
                                            </h3>
                                            <p style="margin: 0; color: #6c757d;">
                                                <strong>Oferta:</strong> <?php echo htmlspecialchars($postulacion['titulo']); ?>
                                            </p>
                                        </div>
                                        <span class="status-cell" style="background: <?php 
                                            echo $postulacion['estado'] == 'Seleccionado' ? '#28a745' : 
                                                ($postulacion['estado'] == 'Rechazado' ? '#dc3545' : 
                                                ($postulacion['estado'] == 'Revisado' ? '#007bff' : '#ffc107')); 
                                        ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.8rem;">
                                            <?php echo $postulacion['estado']; ?>
                                        </span>
                                    </div>

                                    <div style="margin-bottom: 1rem;">
                                        <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; font-size: 0.9rem;">
                                            <div>
                                                <strong>DNI:</strong> <?php echo $postulacion['dni']; ?>
                                            </div>
                                            <div>
                                                <strong>Edad:</strong> <?php echo $postulacion['edad']; ?> años
                                            </div>
                                            <div>
                                                <strong>Correo:</strong> <?php echo htmlspecialchars($postulacion['correo']); ?>
                                            </div>
                                            <div>
                                                <strong>Postulación:</strong> <?php echo formatSqlServerDate($postulacion['fecha_postulacion'], 'd/m/Y H:i'); ?>
                                            </div>
                                        </div>
                                    </div>

                                    <div style="display: flex; gap: 1rem; margin-bottom: 1rem;">
                                        <?php if ($postulacion['foto_perfil']): ?>
                                            <a href="../uploads/<?php echo $postulacion['foto_perfil']; ?>" target="_blank" class="btn btn-sm btn-secondary">
                                                Ver Foto
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($postulacion['cv_archivo']): ?>
                                            <a href="../uploads/<?php echo $postulacion['cv_archivo']; ?>" target="_blank" class="btn btn-sm">
                                                Descargar CV
                                            </a>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($postulacion['comentarios']): ?>
                                        <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px; margin-bottom: 1rem;">
                                            <strong>Comentarios anteriores:</strong>
                                            <p style="margin: 0.5rem 0 0 0;"><?php echo htmlspecialchars($postulacion['comentarios']); ?></p>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <div class="col-4">
                                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                                        <h4>Actualizar Estado</h4>
                                        <form method="POST">
                                            <input type="hidden" name="postulacion_id" value="<?php echo $postulacion['id_postulacion']; ?>">
                                            
                                            <div class="form-group">
                                                <label for="nuevo_estado_<?php echo $postulacion['id_postulacion']; ?>" class="form-label">Estado</label>
                                                <select id="nuevo_estado_<?php echo $postulacion['id_postulacion']; ?>" name="nuevo_estado" class="form-control" required>
                                                    <option value="Pendiente" <?php echo $postulacion['estado'] == 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                                    <option value="Revisado" <?php echo $postulacion['estado'] == 'Revisado' ? 'selected' : ''; ?>>Revisado</option>
                                                    <option value="Seleccionado" <?php echo $postulacion['estado'] == 'Seleccionado' ? 'selected' : ''; ?>>Seleccionado</option>
                                                    <option value="Rechazado" <?php echo $postulacion['estado'] == 'Rechazado' ? 'selected' : ''; ?>>Rechazado</option>
                                                </select>
                                            </div>

                                            <div class="form-group">
                                                <label for="comentarios_<?php echo $postulacion['id_postulacion']; ?>" class="form-label">Comentarios</label>
                                                <textarea id="comentarios_<?php echo $postulacion['id_postulacion']; ?>" name="comentarios" class="form-control" rows="3" 
                                                          placeholder="Comentarios para el estudiante..."><?php echo htmlspecialchars($postulacion['comentarios']); ?></textarea>
                                            </div>

                                            <button type="submit" class="btn btn-sm" style="width: 100%;">Actualizar</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Estadísticas resumen -->
                <div class="card" style="background: #f8f9fa;">
                    <h3>Resumen de Postulaciones</h3>
                    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
                        <div class="text-center">
                            <div style="font-size: 1.5rem; font-weight: bold; color: #ffc107;">
                                <?php echo count(array_filter($postulaciones, function($p) { return $p['estado'] == 'Pendiente'; })); ?>
                            </div>
                            <div style="font-size: 0.9rem;">Pendientes</div>
                        </div>
                        <div class="text-center">
                            <div style="font-size: 1.5rem; font-weight: bold; color: #007bff;">
                                <?php echo count(array_filter($postulaciones, function($p) { return $p['estado'] == 'Revisado'; })); ?>
                            </div>
                            <div style="font-size: 0.9rem;">Revisadas</div>
                        </div>
                        <div class="text-center">
                            <div style="font-size: 1.5rem; font-weight: bold; color: #28a745;">
                                <?php echo count(array_filter($postulaciones, function($p) { return $p['estado'] == 'Seleccionado'; })); ?>
                            </div>
                            <div style="font-size: 0.9rem;">Seleccionadas</div>
                        </div>
                        <div class="text-center">
                            <div style="font-size: 1.5rem; font-weight: bold; color: #dc3545;">
                                <?php echo count(array_filter($postulaciones, function($p) { return $p['estado'] == 'Rechazado'; })); ?>
                            </div>
                            <div style="font-size: 0.9rem;">Rechazadas</div>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="text-center">
                    <p>No hay postulaciones para mostrar.</p>
                    <?php if ($selectedOferta): ?>
                        <p style="color: #6c757d;">No hay postulaciones para esta oferta específica.</p>
                        <a href="applications.php" class="btn">Ver todas las postulaciones</a>
                    <?php else: ?>
                        <p style="color: #6c757d;">Publica ofertas laborales para empezar a recibir postulaciones.</p>
                        <a href="dashboard.php" class="btn">Crear Nueva Oferta</a>
                    <?php endif; ?>
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
