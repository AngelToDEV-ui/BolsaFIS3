<?php
require_once '../includes/config.php';
requireUserType(['admin']);

// Obtener estadísticas
try {
    // Total de estudiantes activos
    $stmt = $db->query("SELECT COUNT(*) as total FROM Estudiantes WHERE activo = 1");
    $totalEstudiantes = $db->fetch($stmt)['total'];
    
    // Total de estudiantes inactivos
    $stmt = $db->query("SELECT COUNT(*) as total FROM Estudiantes WHERE activo = 0");
    $totalEstudiantesInactivos = $db->fetch($stmt)['total'];
    
    // Total de empresas activas
    $stmt = $db->query("SELECT COUNT(*) as total FROM Empresas WHERE activo = 1");
    $totalEmpresas = $db->fetch($stmt)['total'];
    
    // Total de empresas inactivas
    $stmt = $db->query("SELECT COUNT(*) as total FROM Empresas WHERE activo = 0");
    $totalEmpresasInactivas = $db->fetch($stmt)['total'];
    
    // Total de ofertas
    $stmt = $db->query("SELECT COUNT(*) as total FROM OfertasLaborales WHERE activo = 1");
    $totalOfertas = $db->fetch($stmt)['total'];
    
    // Total de postulaciones
    $stmt = $db->query("SELECT COUNT(*) as total FROM Postulaciones");
    $totalPostulaciones = $db->fetch($stmt)['total'];
    
    // Empresas con más postulaciones
    $sql = "SELECT TOP 5 e.nombre, e.ruc, 
                   COUNT(p.id_postulacion) as total_postulaciones
            FROM Empresas e
            LEFT JOIN OfertasLaborales o ON e.id_empresa = o.id_empresa
            LEFT JOIN Postulaciones p ON o.id_oferta = p.id_oferta
            WHERE e.activo = 1
            GROUP BY e.id_empresa, e.nombre, e.ruc
            ORDER BY total_postulaciones DESC";
    $stmt = $db->query($sql);
    $empresasTopPostulaciones = $db->fetchAll($stmt);
    
    // Estudiantes con más postulaciones
    $sql = "SELECT TOP 5 e.nombres, e.apellidos, e.dni,
                   COUNT(p.id_postulacion) as total_postulaciones
            FROM Estudiantes e
            LEFT JOIN Postulaciones p ON e.id_estudiante = p.id_estudiante
            WHERE e.activo = 1
            GROUP BY e.id_estudiante, e.nombres, e.apellidos, e.dni
            ORDER BY total_postulaciones DESC";
    $stmt = $db->query($sql);
    $estudiantesTopPostulaciones = $db->fetchAll($stmt);
    
} catch (Exception $e) {
    $error = 'Error al cargar estadísticas: ' . $e->getMessage();
}

// Manejar acciones
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        if ($action == 'delete_student') {
            $id = intval($_POST['id']);
            // ELIMINACIÓN FÍSICA - Borra permanentemente
            $sql = "DELETE FROM Estudiantes WHERE id_estudiante = ?";
            $db->execute($sql, [$id]);
            showMessage('Estudiante eliminado permanentemente', 'success');
            
        } elseif ($action == 'delete_company') {
            $id = intval($_POST['id']);
            // ELIMINACIÓN FÍSICA - Borra permanentemente
            $sql = "DELETE FROM Empresas WHERE id_empresa = ?";
            $db->execute($sql, [$id]);
            showMessage('Empresa eliminada permanentemente', 'success');
        }
        
        header('Location: dashboard.php');
        exit;
        
    } catch (Exception $e) {
        $error = 'Error: ' . $e->getMessage();
    }
}

$message = getAndClearMessage();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrador - FIS UNCP</title>
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
        <div class="dashboard-header">
            <h1>Dashboard del Administrador</h1>
            <p>Bienvenido, <?php echo $_SESSION['user_name']; ?></p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-<?php echo $message['type']; ?>">
                <?php echo $message['message']; ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error)): ?>
            <div class="alert alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Estadísticas generales -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalEstudiantes; ?></div>
                <div class="stat-label">✅ Estudiantes Activos</div>
                <?php if ($totalEstudiantesInactivos > 0): ?>
                    <div style="font-size: 0.8rem; color: #dc3545; margin-top: 5px;">
                        ❌ Inactivos: <?php echo $totalEstudiantesInactivos; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalEmpresas; ?></div>
                <div class="stat-label">✅ Empresas Activas</div>
                <?php if ($totalEmpresasInactivas > 0): ?>
                    <div style="font-size: 0.8rem; color: #dc3545; margin-top: 5px;">
                        ❌ Inactivas: <?php echo $totalEmpresasInactivas; ?>
                    </div>
                <?php endif; ?>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalOfertas; ?></div>
                <div class="stat-label">Ofertas Laborales</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $totalPostulaciones; ?></div>
                <div class="stat-label">Total Postulaciones</div>
            </div>
        </div>

        <div class="row">
            <!-- Empresas con más postulaciones -->
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Empresas con Más Postulaciones</h2>
                    </div>
                    
                    <?php if (!empty($empresasTopPostulaciones)): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Empresa</th>
                                    <th>RUC</th>
                                    <th>Postulaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($empresasTopPostulaciones as $empresa): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($empresa['nombre']); ?></td>
                                        <td><?php echo $empresa['ruc']; ?></td>
                                        <td><?php echo $empresa['total_postulaciones']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No hay datos disponibles</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Estudiantes con más postulaciones -->
            <div class="col-6">
                <div class="card">
                    <div class="card-header">
                        <h2 class="card-title">Estudiantes con Más Postulaciones</h2>
                    </div>
                    
                    <?php if (!empty($estudiantesTopPostulaciones)): ?>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Estudiante</th>
                                    <th>DNI</th>
                                    <th>Postulaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($estudiantesTopPostulaciones as $estudiante): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($estudiante['nombres'] . ' ' . $estudiante['apellidos']); ?></td>
                                        <td><?php echo $estudiante['dni']; ?></td>
                                        <td><?php echo $estudiante['total_postulaciones']; ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p>No hay datos disponibles</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Acciones rápidas -->
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Acciones Rápidas</h2>
            </div>
            
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="students.php" class="btn">Ver Todos los Estudiantes</a>
                <a href="companies.php" class="btn btn-secondary">Ver Todas las Empresas</a>
                <button onclick="AdminDashboard.generateReports()" class="btn" style="background-color: #28a745; color: white;">
                    📊 Generar Reportes
                </button>
                <button onclick="testReportsDirectly()" class="btn" style="background-color: #17a2b8; color: white;">
                    🔬 Test Directo
                </button>
                <a href="../test_reports.php" class="btn" style="background-color: #6c757d; color: white;" target="_blank">
                    🔧 Diagnóstico
                </a>
            </div>
            
            <!-- Área para mostrar resultados de reportes -->
            <div id="reports-result" style="margin-top: 20px;"></div>
            
            <script>
            function testReportsDirectly() {
                const resultDiv = document.getElementById('reports-result');
                resultDiv.innerHTML = '<div class="alert alert-info">🔬 Probando generación directa...</div>';
                
                fetch('../reports/generate_reports_fixed.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=generate_reports'
                })
                .then(response => response.text())
                .then(text => {
                    console.log('Response text:', text);
                    try {
                        const data = JSON.parse(text);
                        if (data.success) {
                            resultDiv.innerHTML = `<div class="alert alert-success">
                                <h4>✅ Test Directo Exitoso!</h4>
                                <p><a href="../${data.ruta}" target="_blank" class="btn" style="background-color: #28a745; color: white;">Ver Reporte</a></p>
                                <details><summary>Debug Info</summary><pre>${JSON.stringify(data.debug, null, 2)}</pre></details>
                            </div>`;
                        } else {
                            resultDiv.innerHTML = `<div class="alert alert-error">❌ Error: ${data.message}<br><pre>${JSON.stringify(data, null, 2)}</pre></div>`;
                        }
                    } catch (e) {
                        resultDiv.innerHTML = `<div class="alert alert-error">❌ Parse Error: ${e.message}<br><pre>${text}</pre></div>`;
                    }
                })
                .catch(error => {
                    resultDiv.innerHTML = `<div class="alert alert-error">❌ Network Error: ${error.message}</div>`;
                });
            }
            </script>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; 2025 Facultad de Ingeniería de Sistemas - UNCP</p>
    </footer>

    <script src="../public/js/main.js"></script>
</body>
</html>
