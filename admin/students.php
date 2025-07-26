<?php
require_once '../includes/config.php';
requireUserType(['admin']);

// Obtener filtro
$filtro = $_GET['filtro'] ?? 'todos';

// Obtener lista de estudiantes según filtro
try {
    $whereCondition = '';
    switch ($filtro) {
        case 'activos':
            $whereCondition = 'WHERE e.activo = 1';
            break;
        case 'inactivos':
            $whereCondition = 'WHERE e.activo = 0';
            break;
        case 'todos':
        default:
            $whereCondition = ''; // Sin filtro, mostrar todos
            break;
    }
    
    $sql = "SELECT e.id_estudiante, e.nombres, e.apellidos, e.dni, e.correo, 
                   e.anio_nacimiento, e.fecha_registro, e.activo,
                   dbo.CalcularEdad(e.anio_nacimiento) as edad,
                   COUNT(p.id_postulacion) as total_postulaciones
            FROM Estudiantes e
            LEFT JOIN Postulaciones p ON e.id_estudiante = p.id_estudiante
            $whereCondition
            GROUP BY e.id_estudiante, e.nombres, e.apellidos, e.dni, e.correo, 
                     e.anio_nacimiento, e.fecha_registro, e.activo
            ORDER BY e.fecha_registro DESC";
    
    $stmt = $db->query($sql);
    $estudiantes = $db->fetchAll($stmt);
    
} catch (Exception $e) {
    $error = 'Error al cargar estudiantes: ' . $e->getMessage();
}

// Manejar cambio de estado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'toggle_status') {
    try {
        $id = intval($_POST['id']);
        $nuevoEstado = intval($_POST['nuevo_estado']);
        $accion = $nuevoEstado ? 'reactivado' : 'desactivado';
        
        $sql = "UPDATE Estudiantes SET activo = ? WHERE id_estudiante = ?";
        $db->execute($sql, [$nuevoEstado, $id]);
        showMessage("Estudiante $accion exitosamente", 'success');
        header('Location: students.php?filtro=' . $filtro);
        exit;
    } catch (Exception $e) {
        $error = 'Error al cambiar estado del estudiante: ' . $e->getMessage();
    }
}

$message = getAndClearMessage();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Estudiantes - Admin FIS UNCP</title>
    <link rel="stylesheet" href="../public/css/styles.css">
</head>
<body>
    <header class="header">
        <div class="header-container">
            <a href="dashboard.php" class="logo">
                <img src="../public/images/logo-uncp.png" alt="Logo FIS" class="logo-faculty">
                <span class="logo-text">FIS-UNCP Admin</span>
            </a>
            <nav>
                <ul class="nav-menu">
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="students.php">Estudiantes</a></li>
                    <li><a href="companies.php">Empresas</a></li>
                    <li><a href="../logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h1 class="card-title">Gestión de Estudiantes</h1>
                <p>Total de estudiantes: <?php echo count($estudiantes); ?></p>
                
                <!-- Filtros -->
                <div style="margin: 20px 0; display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="students.php?filtro=todos" 
                       class="btn <?php echo $filtro === 'todos' ? '' : 'btn-secondary'; ?>">
                        📋 Todos (<?php 
                            $stmt = $db->query("SELECT COUNT(*) as total FROM Estudiantes");
                            echo $db->fetch($stmt)['total'];
                        ?>)
                    </a>
                    <a href="students.php?filtro=activos" 
                       class="btn <?php echo $filtro === 'activos' ? '' : 'btn-secondary'; ?>">
                        ✅ Activos (<?php 
                            $stmt = $db->query("SELECT COUNT(*) as total FROM Estudiantes WHERE activo = 1");
                            echo $db->fetch($stmt)['total'];
                        ?>)
                    </a>
                    <a href="students.php?filtro=inactivos" 
                       class="btn <?php echo $filtro === 'inactivos' ? '' : 'btn-secondary'; ?>">
                        ❌ Inactivos (<?php 
                            $stmt = $db->query("SELECT COUNT(*) as total FROM Estudiantes WHERE activo = 0");
                            echo $db->fetch($stmt)['total'];
                        ?>)
                    </a>
                </div>
            </div>

            <?php if ($message): ?>
                <div class="alert alert-<?php echo $message['type']; ?>">
                    <?php echo $message['message']; ?>
                </div>
            <?php endif; ?>

            <?php if (isset($error)): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>

            <?php if (!empty($estudiantes)): ?>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Estado</th>
                                <th>Nombre Completo</th>
                                <th>DNI</th>
                                <th>Correo</th>
                                <th>Edad</th>
                                <th>Postulaciones</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estudiantes as $estudiante): ?>
                                <tr style="<?php echo $estudiante['activo'] ? '' : 'opacity: 0.6; background-color: #f8f9fa;'; ?>">
                                    <td><?php echo $estudiante['id_estudiante']; ?></td>
                                    <td>
                                        <?php if ($estudiante['activo']): ?>
                                            <span style="color: #28a745; font-weight: bold;">✅ Activo</span>
                                        <?php else: ?>
                                            <span style="color: #dc3545; font-weight: bold;">❌ Inactivo</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']); ?></td>
                                    <td><?php echo $estudiante['dni']; ?></td>
                                    <td><?php echo htmlspecialchars($estudiante['correo']); ?></td>
                                    <td><?php echo $estudiante['edad']; ?> años</td>
                                    <td><?php echo $estudiante['total_postulaciones']; ?></td>
                                    <td><?php echo formatSqlServerDate($estudiante['fecha_registro'], 'd/m/Y'); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="id" value="<?php echo $estudiante['id_estudiante']; ?>">
                                            <input type="hidden" name="nuevo_estado" value="<?php echo $estudiante['activo'] ? 0 : 1; ?>">
                                            
                                            <?php if ($estudiante['activo']): ?>
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        data-confirm="¿Está seguro de que desea desactivar al estudiante <?php echo htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']); ?>?">
                                                    🚫 Desactivar
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-success btn-sm" 
                                                        data-confirm="¿Está seguro de que desea reactivar al estudiante <?php echo htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']); ?>?">
                                                    ✅ Reactivar
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center">
                    <p>No hay estudiantes registrados.</p>
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
