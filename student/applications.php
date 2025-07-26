<?php
require_once '../includes/config.php';
requireUserType(['student']);

$studentId = $_SESSION['user_id'];

// Obtener postulaciones del estudiante
try {
    $sql = "SELECT p.*, o.titulo, o.descripcion, o.modalidad, o.ubicacion, 
                   o.salario_min, o.salario_max, o.id_empresa, e.nombre as empresa_nombre
            FROM Postulaciones p
            INNER JOIN OfertasLaborales o ON p.id_oferta = o.id_oferta
            INNER JOIN Empresas e ON o.id_empresa = e.id_empresa
            WHERE p.id_estudiante = ?
            ORDER BY p.fecha_postulacion DESC";
    
    $stmt = $db->query($sql, [$studentId]);
    $postulaciones = $db->fetchAll($stmt);
    
} catch (Exception $e) {
    $error = 'Error al cargar postulaciones: ' . $e->getMessage();
}

$message = getAndClearMessage();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Postulaciones - FIS UNCP</title>
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
                <h1 class="card-title">Mis Postulaciones</h1>
                <p>Historial completo de tus postulaciones laborales</p>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($postulaciones)): ?>
                <div class="mb-3">
                    <p>Total de postulaciones: <strong><?php echo count($postulaciones); ?></strong></p>
                </div>

                <?php foreach ($postulaciones as $postulacion): ?>
                    <div class="card" style="margin-bottom: 1.5rem; border-left: 4px solid <?php 
                        echo $postulacion['estado'] == 'Seleccionado' ? '#28a745' : 
                            ($postulacion['estado'] == 'Rechazado' ? '#dc3545' : 
                            ($postulacion['estado'] == 'Revisado' ? '#007bff' : '#ffc107')); ?>;">
                        
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h3 style="color: #1e3c72; margin: 0 0 0.5rem 0;">
                                    <?php echo htmlspecialchars($postulacion['titulo']); ?>
                                </h3>
                                <p style="margin: 0; font-weight: 500;">
                                    <a href="../company/public_profile.php?id=<?php echo $postulacion['id_empresa']; ?>" 
                                       class="company-link">
                                        <?php echo htmlspecialchars($postulacion['empresa_nombre']); ?>
                                    </a>
                                </p>
                            </div>
                            <div style="text-align: right;">
                                <span style="background: <?php 
                                    echo $postulacion['estado'] == 'Seleccionado' ? '#28a745' : 
                                        ($postulacion['estado'] == 'Rechazado' ? '#dc3545' : 
                                        ($postulacion['estado'] == 'Revisado' ? '#007bff' : '#ffc107')); 
                                ?>; color: white; padding: 0.25rem 0.75rem; border-radius: 15px; font-size: 0.8rem;">
                                    <?php echo $postulacion['estado']; ?>
                                </span>
                                <div style="font-size: 0.8rem; color: #6c757d; margin-top: 0.5rem;">
                                    Postulado: <?php echo formatSqlServerDate($postulacion['fecha_postulacion'], 'd/m/Y H:i'); ?>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-8">
                                <p style="color: #6c757d; margin-bottom: 1rem;">
                                    <?php echo htmlspecialchars(substr($postulacion['descripcion'], 0, 200)) . (strlen($postulacion['descripcion']) > 200 ? '...' : ''); ?>
                                </p>
                                
                                <div style="display: flex; gap: 1rem; flex-wrap: wrap; font-size: 0.9rem;">
                                    <span style="background: #e9ecef; padding: 0.25rem 0.5rem; border-radius: 3px;">
                                        📍 <?php echo htmlspecialchars($postulacion['modalidad']); ?>
                                    </span>
                                    <?php if ($postulacion['ubicacion']): ?>
                                        <span style="background: #e9ecef; padding: 0.25rem 0.5rem; border-radius: 3px;">
                                            🌍 <?php echo htmlspecialchars($postulacion['ubicacion']); ?>
                                        </span>
                                    <?php endif; ?>
                                    <?php if ($postulacion['salario_min'] && $postulacion['salario_max']): ?>
                                        <span style="background: #e9ecef; padding: 0.25rem 0.5rem; border-radius: 3px;">
                                            💰 S/<?php echo number_format($postulacion['salario_min'], 0); ?> - S/<?php echo number_format($postulacion['salario_max'], 0); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="col-4">
                                <?php if ($postulacion['comentarios']): ?>
                                    <div style="background: #f8f9fa; padding: 1rem; border-radius: 5px;">
                                        <strong style="font-size: 0.9rem;">Comentarios de la empresa:</strong>
                                        <p style="margin: 0.5rem 0 0 0; font-size: 0.9rem; color: #495057;">
                                            <?php echo htmlspecialchars($postulacion['comentarios']); ?>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

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
                    <p>Aún no has realizado ninguna postulación.</p>
                    <a href="dashboard.php" class="btn">Ver Ofertas Disponibles</a>
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
