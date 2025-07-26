<?php
require_once '../includes/config.php';
requireUserType(['admin']);

// Obtener filtro
$filtro = $_GET['filtro'] ?? 'todos';

// Obtener lista de empresas según filtro
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
    
    $sql = "SELECT e.id_empresa, e.nombre, e.ruc, e.correo, e.descripcion,
                   e.fecha_registro, e.activo,
                   COUNT(DISTINCT o.id_oferta) as total_ofertas,
                   COUNT(p.id_postulacion) as total_postulaciones
            FROM Empresas e
            LEFT JOIN OfertasLaborales o ON e.id_empresa = o.id_empresa
            LEFT JOIN Postulaciones p ON o.id_oferta = p.id_oferta
            $whereCondition
            GROUP BY e.id_empresa, e.nombre, e.ruc, e.correo, e.descripcion, e.fecha_registro, e.activo
            ORDER BY e.fecha_registro DESC";
    
    $stmt = $db->query($sql);
    $empresas = $db->fetchAll($stmt);
    
} catch (Exception $e) {
    $error = 'Error al cargar empresas: ' . $e->getMessage();
}

// Manejar cambio de estado
if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_POST['action'] == 'toggle_status') {
    try {
        $id = intval($_POST['id']);
        $nuevoEstado = intval($_POST['nuevo_estado']);
        $accion = $nuevoEstado ? 'reactivada' : 'desactivada';
        
        $sql = "UPDATE Empresas SET activo = ? WHERE id_empresa = ?";
        $db->execute($sql, [$nuevoEstado, $id]);
        showMessage("Empresa $accion exitosamente", 'success');
        header('Location: companies.php?filtro=' . $filtro);
        exit;
    } catch (Exception $e) {
        $error = 'Error al cambiar estado de la empresa: ' . $e->getMessage();
    }
}

$message = getAndClearMessage();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Empresas - Admin FIS UNCP</title>
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
                <h1 class="card-title">Gestión de Empresas</h1>
                <p>Total de empresas: <?php echo count($empresas); ?></p>
                
                <!-- Filtros -->
                <div style="margin: 20px 0; display: flex; gap: 10px; flex-wrap: wrap;">
                    <a href="companies.php?filtro=todos" 
                       class="btn <?php echo $filtro === 'todos' ? '' : 'btn-secondary'; ?>">
                        📋 Todas (<?php 
                            $stmt = $db->query("SELECT COUNT(*) as total FROM Empresas");
                            echo $db->fetch($stmt)['total'];
                        ?>)
                    </a>
                    <a href="companies.php?filtro=activos" 
                       class="btn <?php echo $filtro === 'activos' ? '' : 'btn-secondary'; ?>">
                        ✅ Activas (<?php 
                            $stmt = $db->query("SELECT COUNT(*) as total FROM Empresas WHERE activo = 1");
                            echo $db->fetch($stmt)['total'];
                        ?>)
                    </a>
                    <a href="companies.php?filtro=inactivos" 
                       class="btn <?php echo $filtro === 'inactivos' ? '' : 'btn-secondary'; ?>">
                        ❌ Inactivas (<?php 
                            $stmt = $db->query("SELECT COUNT(*) as total FROM Empresas WHERE activo = 0");
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

            <?php if (!empty($empresas)): ?>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Estado</th>
                                <th>Nombre</th>
                                <th>RUC</th>
                                <th>Correo</th>
                                <th>Ofertas</th>
                                <th>Postulaciones</th>
                                <th>Fecha Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($empresas as $empresa): ?>
                                <tr style="<?php echo $empresa['activo'] ? '' : 'opacity: 0.6; background-color: #f8f9fa;'; ?>">
                                    <td><?php echo $empresa['id_empresa']; ?></td>
                                    <td>
                                        <?php if ($empresa['activo']): ?>
                                            <span style="color: #28a745; font-weight: bold;">✅ Activa</span>
                                        <?php else: ?>
                                            <span style="color: #dc3545; font-weight: bold;">❌ Inactiva</span>
                                        <?php endif; ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($empresa['nombre']); ?></td>
                                    <td><?php echo $empresa['ruc']; ?></td>
                                    <td><?php echo htmlspecialchars($empresa['correo']); ?></td>
                                    <td><?php echo $empresa['total_ofertas']; ?></td>
                                    <td><?php echo $empresa['total_postulaciones']; ?></td>
                                    <td><?php echo formatSqlServerDate($empresa['fecha_registro'], 'd/m/Y'); ?></td>
                                    <td>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <input type="hidden" name="id" value="<?php echo $empresa['id_empresa']; ?>">
                                            <input type="hidden" name="nuevo_estado" value="<?php echo $empresa['activo'] ? 0 : 1; ?>">
                                            
                                            <?php if ($empresa['activo']): ?>
                                                <button type="submit" class="btn btn-danger btn-sm" 
                                                        data-confirm="¿Está seguro de que desea desactivar la empresa <?php echo htmlspecialchars($empresa['nombre']); ?>?">
                                                    🚫 Desactivar
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn btn-success btn-sm" 
                                                        data-confirm="¿Está seguro de que desea reactivar la empresa <?php echo htmlspecialchars($empresa['nombre']); ?>?">
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
                    <p>No hay empresas registradas.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Mostrar detalles de empresas en cards para mejor vista -->
        <div class="row">
            <?php foreach (array_slice($empresas, 0, 6) as $empresa): ?>
                <div class="col-6">
                    <div class="card" style="<?php echo $empresa['activo'] ? '' : 'opacity: 0.7; border-left: 4px solid #dc3545;'; ?>">
                        <div class="card-header">
                            <h3 class="card-title">
                                <?php echo htmlspecialchars($empresa['nombre']); ?>
                                <?php if ($empresa['activo']): ?>
                                    <span style="color: #28a745; font-size: 0.8rem;">✅ Activa</span>
                                <?php else: ?>
                                    <span style="color: #dc3545; font-size: 0.8rem;">❌ Inactiva</span>
                                <?php endif; ?>
                            </h3>
                        </div>
                        <p><strong>RUC:</strong> <?php echo $empresa['ruc']; ?></p>
                        <p><strong>Correo:</strong> <?php echo htmlspecialchars($empresa['correo']); ?></p>
                        <p><strong>Ofertas publicadas:</strong> <?php echo $empresa['total_ofertas']; ?></p>
                        <p><strong>Total postulaciones:</strong> <?php echo $empresa['total_postulaciones']; ?></p>
                        
                        <?php if ($empresa['descripcion']): ?>
                            <p><strong>Descripción:</strong></p>
                            <p style="font-size: 0.9rem; color: #6c757d;">
                                <?php echo htmlspecialchars(substr($empresa['descripcion'], 0, 150)) . (strlen($empresa['descripcion']) > 150 ? '...' : ''); ?>
                            </p>
                        <?php endif; ?>
                        
                        <p><strong>Registrada:</strong> <?php echo formatSqlServerDate($empresa['fecha_registro'], 'd/m/Y'); ?></p>
                        
                        <!-- Botón de acción rápida -->
                        <form method="POST" style="margin-top: 10px;">
                            <input type="hidden" name="action" value="toggle_status">
                            <input type="hidden" name="id" value="<?php echo $empresa['id_empresa']; ?>">
                            <input type="hidden" name="nuevo_estado" value="<?php echo $empresa['activo'] ? 0 : 1; ?>">
                            
                            <?php if ($empresa['activo']): ?>
                                <button type="submit" class="btn btn-danger btn-sm" 
                                        data-confirm="¿Desactivar <?php echo htmlspecialchars($empresa['nombre']); ?>?">
                                    🚫 Desactivar
                                </button>
                            <?php else: ?>
                                <button type="submit" class="btn btn-success btn-sm" 
                                        data-confirm="¿Reactivar <?php echo htmlspecialchars($empresa['nombre']); ?>?">
                                    ✅ Reactivar
                                </button>
                            <?php endif; ?>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Facultad de Ingeniería de Sistemas - UNCP</p>
    </footer>

    <script src="../public/js/main.js"></script>
</body>
</html>
