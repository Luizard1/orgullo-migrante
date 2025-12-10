<?php
session_start();
include 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.html");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['nombre'];

// --- CONSULTAS PARA ESTADÍSTICAS REALES ---

// 1. Total de Trámites
$sql_total = "SELECT COUNT(*) as total FROM solicitudes WHERE usuario_id = $usuario_id";
$res_total = $conn->query($sql_total);
$total_tramites = $res_total->fetch_assoc()['total'];

// 2. Aprobados (Estado: listo_activacion o activo)
$sql_aprobados = "SELECT COUNT(*) as total FROM solicitudes WHERE usuario_id = $usuario_id AND (estado_tramite = 'activo' OR estado_tramite = 'listo_activacion')";
$res_aprobados = $conn->query($sql_aprobados);
$total_aprobados = $res_aprobados->fetch_assoc()['total'];

// 3. En Revisión (Estado: registro_inicial)
$sql_revision = "SELECT COUNT(*) as total FROM solicitudes WHERE usuario_id = $usuario_id AND estado_tramite = 'registro_inicial'";
$res_revision = $conn->query($sql_revision);
$total_revision = $res_revision->fetch_assoc()['total'];

// 4. Pendientes (Estado: pendiente_aclaracion)
$sql_pendientes = "SELECT COUNT(*) as total FROM solicitudes WHERE usuario_id = $usuario_id AND estado_tramite = 'pendiente_aclaracion'";
$res_pendientes = $conn->query($sql_pendientes);
$total_pendientes = $res_pendientes->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padrón Orgullo Migrante - Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css"> </head>
<body class="dashboard-body">

    <header class="dashboard-header">
        <div class="header-content">
            <div class="logo-area">
                <img src="https://cdn-icons-png.flaticon.com/512/263/263115.png" alt="Escudo" class="header-logo">
                <div>
                    <h1>Padrón Orgullo Migrante</h1>
                    <p>Bienvenido, <?php echo htmlspecialchars($nombre_usuario); ?></p>
                </div>
            </div>
            <a href="logout.php" class="logout-btn">Cerrar Sesión</a>
        </div>
    </header>

    <div class="main-container dashboard-container">
        
       <div class="stats-grid">
            
            <div class="stat-card">
                <h3>Total de Trámites</h3>
                <p class="stat-number"><?php echo $total_tramites; ?></p>
            </div>

            <div class="stat-card">
                <h3>Aprobados</h3>
                <p class="stat-number text-green"><?php echo $total_aprobados; ?></p>
            </div>

            <div class="stat-card">
                <h3>En Revisión</h3>
                <p class="stat-number text-blue"><?php echo $total_revision; ?></p>
            </div>

            <div class="stat-card">
                <h3>Pendientes</h3>
                <p class="stat-number text-orange"><?php echo $total_pendientes; ?></p>
            </div>

        </div>

        <div class="action-tabs">
            <button class="tab-btn active">⬆ Cargar Documentos</button>
           <button class="tab-btn" onclick="window.location.href='mis_tramites.php'">
    📄 Mis Trámites
</button>
            <button class="tab-btn">🔔 Notificaciones <span class="badge">3</span></button>
        </div>

        <div class="form-card">
            <h2>Nueva Solicitud de Registro</h2>
            <p class="subtitle">Completa el formulario y carga los documentos oficiales requeridos.</p>

            <div class="info-box">
                <strong>⚠ Importante:</strong> Asegúrate de que todos los documentos sean legibles y estén vigentes (PDF, JPG, PNG).
            </div>

            <form action="procesar_solicitud.php" method="POST" enctype="multipart/form-data">
                
                <div class="form-grid">
                    <div class="input-group">
                        <label>CURP *</label>
                        <input type="file" name="archivo_curp" required accept=".pdf,.jpg,.png">
                    </div>
                    
                    <div class="input-group">
                        <label>RFC *</label>
                        <input type="file" name="archivo_rfc" required accept=".pdf,.jpg,.png">
                    </div>

                    <div class="input-group">
                        <label>Comprobante de Domicilio *</label>
                        <input type="file" name="archivo_domicilio" required accept=".pdf,.jpg,.png">
                    </div>

                    <div class="input-group">
                        <label>Evidencia Adicional</label>
                        <input type="file" name="archivo_evidencia" accept=".pdf,.jpg,.png">
                    </div>
                </div>

                <div class="input-group full-width">
                    <label>Tipo de Trámite *</label>
                    <select name="tipo_tramite" required>
                        <option value="">Seleccione un tipo de trámite</option>
                        <option value="registro_inicial">Registro Inicial al Padrón</option>
                        <option value="renovacion">Renovación de Folio</option>
                    </select>
                </div>

                <div class="input-group full-width">
                    <label>Observaciones (Opcional)</label>
                    <textarea name="observaciones" rows="3" placeholder="Incluye cualquier información adicional..."></textarea>
                </div>

                <button type="submit" class="submit-btn full-btn">Enviar Solicitud</button>
            </form>
            
        </div>
    </div>

</body>
</html>