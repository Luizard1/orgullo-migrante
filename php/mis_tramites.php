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
                   <button class="tab-btn" onclick="window.location.href='dashboard_ciudadano.php'">
    ⬆ Cargar Documentos
</button>
      <button class="tab-btn active"> 
        📄 Mis Trámites
    </button>
            <button class="tab-btn">🔔 Notificaciones <span class="badge">3</span></button>
        </div>

       <div id="lista-tramites" style="margin-top: 40px;">
    <h2 style="margin-bottom: 20px;">Mis Trámites Recientes</h2>

    <?php
    // 1. Buscar todos los trámites de este usuario
    $sql_tramites = "SELECT * FROM solicitudes WHERE usuario_id = $usuario_id ORDER BY fecha_creacion DESC";
    $res_tramites = $conn->query($sql_tramites);

    if ($res_tramites->num_rows > 0) {
        while ($row = $res_tramites->fetch_assoc()) {
            
            // Variables básicas
            $folio = "POM-2025-" . str_pad($row['id'], 5, "0", STR_PAD_LEFT);
            $fecha_sol = date("d/m/Y", strtotime($row['fecha_creacion']));
            $fecha_act = date("d/m/Y", strtotime($row['fecha_ultimo_cambio']));
            $comentario = $row['ultimos_comentarios'];
            
            // Lógica de Estado (Colores y Textos)
            $estado_texto = "";
            $clase_color = "";
            $icono = "";

           switch (trim(strtolower($row['estado_tramite']))) { 
    // Usamos trim() y strtolower() para ignorar mayúsculas y espacios extra
    
    case 'activo':
    case 'listo_activacion':
        $estado_texto = "Aprobado";
        $clase_color = "bg-green";
        $icono = "check-circle";
        $msg_default = "¡Felicidades! Tu folio ha sido aprobado.";
        break;
    
    case 'registro_inicial':
        $estado_texto = "En Revisión";
        $clase_color = "bg-black";
        $icono = "clock";
        $msg_default = "Tu solicitud está siendo analizada.";
        break;

    case 'pendiente_aclaracion':
        $estado_texto = "Aclaración Requerida";
        $clase_color = "bg-orange";
        $icono = "exclamation-circle";
        $msg_default = "Se requiere información adicional.";
        break;

    // --- AQUÍ ESTÁ EL ARREGLO ---
    case 'baja':
    case 'devuelto': 
        $estado_texto = "Devuelto";
        $clase_color = "bg-red";  // Asegúrate de tener esta clase en tu CSS
        $icono = "times-circle";
        $msg_default = "El trámite ha sido devuelto. Lee las notas abajo.";
        break;

    // --- CÓDIGO DE SEGURIDAD (DEFAULT) ---
    // Si la base de datos tiene un nombre raro, caerá aquí y te lo mostrará
    default:
      case 'devuelto': 
        $estado_texto = "Devuelto";
        $clase_color = "bg-red";  // Asegúrate de tener esta clase en tu CSS
        $icono = "times-circle";
        $msg_default = "El trámite ha sido devuelto. Lee las notas abajo.";
        break;
}
    ?>

    <div class="tramite-card">
        
        <div class="tramite-header">
            <div>
                <div class="tramite-title">Registro Padrón Orgullo Migrante</div>
                <div class="tramite-folio">Folio: <?php echo $folio; ?></div>
            </div>
            <div class="status-pill <?php echo $clase_color; ?>">
                <i class="fas fa-<?php echo $icono; ?>"></i> <?php echo $estado_texto; ?>
            </div>
        </div>

        <div class="tramite-fechas">
            <div>
                <div class="fecha-label">Fecha de Solicitud</div>
                <div class="fecha-valor"><?php echo $fecha_sol; ?></div>
            </div>
            <div>
                <div class="fecha-label">Última Actualización</div>
                <div class="fecha-valor"><?php echo $fecha_act; ?></div>
            </div>
        </div>

        <div class="mensaje-revisor">
            <i class="fas fa-info-circle mensaje-icon"></i>
            <div>
                <?php 
                // Si hay un comentario del revisor, muéstralo. Si no, mensaje por defecto.
                if (!empty($comentario)) {
                    echo "<strong>Nota del Revisor:</strong> " . htmlspecialchars($comentario);
                } else {
                    echo $msg_default;
                }
                ?>
            </div>
        </div>

        <?php if ($row['estado_tramite'] == 'pendiente_aclaracion') { ?>
            <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px;">
                <p style="font-size: 13px; color: #f97316; font-weight: 600; margin-bottom: 5px;">Subir Aclaración:</p>
                <form action="procesar_aclaracion.php" method="POST" enctype="multipart/form-data" style="display:flex; gap:10px;">
                    <input type="hidden" name="solicitud_id" value="<?php echo $row['id']; ?>">
                    <input type="file" name="archivo_aclaracion" required style="font-size: 12px;">
                    <button type="submit" class="submit-btn" style="width: auto; padding: 5px 15px; background: #f97316;">Enviar</button>
                </form>
            </div>
        <?php } ?>

    </div>
    <?php 
        } // Fin del While
    } else {
        echo "<p style='color:#666;'>Aún no tienes trámites registrados.</p>";
    }
    ?>
</div>
            
        </div>
    </div>

</body>
</html>