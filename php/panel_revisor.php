<?php
session_start();
include 'db.php';

// Verificar que sea revisor
if (!isset($_SESSION['usuario_id'])) { // Aquí podrías agregar && $_SESSION['rol'] == 'revisor'
    header("Location: index.html");
    exit();
}

$nombre_revisor = $_SESSION['nombre'];

// --- ESTADÍSTICAS GLOBALES ---
// Total
$r1 = $conn->query("SELECT COUNT(*) as c FROM solicitudes");
$stat_total = $r1->fetch_assoc()['c'];

// En Revisión
$r2 = $conn->query("SELECT COUNT(*) as c FROM solicitudes WHERE estado_tramite='registro_inicial'");
$stat_revision = $r2->fetch_assoc()['c'];

// Pendientes (Aclaraciones)
$r3 = $conn->query("SELECT COUNT(*) as c FROM solicitudes WHERE estado_tramite='pendiente_aclaracion'");
$stat_pendientes = $r3->fetch_assoc()['c'];

// Revisadas Hoy (Cualquier cambio de estado hecho hoy)
$hoy = date('Y-m-d');
$r4 = $conn->query("SELECT COUNT(*) as c FROM solicitudes WHERE DATE(fecha_ultimo_cambio) = '$hoy' AND estado_tramite != 'registro_inicial'");
$stat_hoy = $r4->fetch_assoc()['c'];

$sql_total = "SELECT COUNT(*) as c FROM solicitudes";
$res_total = $conn->query($sql_total);
$total_solicitudes = $res_total->fetch_assoc()['c'];

// 2. En Revisión (Las que requieren tu atención inmediata)
$sql_revision = "SELECT COUNT(*) as c FROM solicitudes WHERE estado_tramite = 'registro_inicial'";
$res_revision = $conn->query($sql_revision);
$total_revision = $res_revision->fetch_assoc()['c'];

// 3. Pendientes (Las que devolviste o pediste aclaración)
$sql_pendientes = "SELECT COUNT(*) as c FROM solicitudes WHERE estado_tramite = 'pendiente_aclaracion'";
$res_pendientes = $conn->query($sql_pendientes);
$total_pendientes = $res_pendientes->fetch_assoc()['c'];

// 4. Revisadas Hoy (Productividad del día)
// Cuenta las que cambiaron de estado hoy y YA NO están en registro inicial
$hoy = date('Y-m-d');
$sql_hoy = "SELECT COUNT(*) as c FROM solicitudes WHERE DATE(fecha_ultimo_cambio) = '$hoy' AND estado_tramite != 'registro_inicial'";
$res_hoy = $conn->query($sql_hoy);
$total_hoy = $res_hoy->fetch_assoc()['c'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel del Revisor - Padrón Orgullo Migrante</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="dashboard-body">

    <header class="dashboard-header">
        <div class="header-content">
            <div class="logo-area">
                <img src="https://cdn-icons-png.flaticon.com/512/263/263115.png" alt="Escudo" class="header-logo">
                <div>
                    <h1>Padrón Orgullo Migrante - Panel del Revisor</h1>
                    <p style="font-size: 12px; opacity: 0.8;">Revisor: <?php echo htmlspecialchars($nombre_revisor); ?></p>
                </div>
            </div>
            <a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
        </div>
    </header>

    <div class="main-container dashboard-container">
        
      <div class="stats-grid">
            
            <div class="stat-card">
                <h3>Solicitudes Totales</h3>
                <p class="stat-number text-green"><?php echo $total_solicitudes; ?></p>
            </div>

            <div class="stat-card">
                <h3>En Revisión</h3>
                <p class="stat-number text-blue"><?php echo $total_revision; ?></p>
            </div>

            <div class="stat-card">
                <h3>Pendientes</h3>
                <p class="stat-number text-orange"><?php echo $total_pendientes; ?></p>
            </div>

            <div class="stat-card">
                <h3>Revisadas Hoy</h3>
                <p class="stat-number"><?php echo $total_hoy; ?></p>
            </div>

        </div>
      

        <h2 style="margin-bottom: 20px; color: #1f2937;">Solicitudes Pendientes</h2>

        <?php
        // 1. Obtener solicitudes pendientes
        $sql = "SELECT s.id, u.nombre_completo, s.fecha_creacion, s.estado_tramite 
                FROM solicitudes s 
                JOIN usuarios u ON s.usuario_id = u.id 
                WHERE s.estado_tramite = 'registro_inicial' OR s.estado_tramite = 'pendiente_aclaracion'
                ORDER BY s.fecha_creacion DESC";
        
        $result = $conn->query($sql);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $folio = "POM-2025-" . str_pad($row['id'], 5, "0", STR_PAD_LEFT);
                $solicitud_id = $row['id'];
        ?>

        <div class="request-card">
            
            <div class="req-header">
                <div class="req-title">
                    <h3>Actualización de Datos Personales</h3>
                    <div class="req-subtitle">
                        Folio: <strong><?php echo $folio; ?></strong> • Ciudadano: <?php echo $row['nombre_completo']; ?>
                    </div>
                </div>
                <div class="status-badge">
                    <i class="fas fa-file-alt"></i> En Revisión
                </div>
            </div>

            <div class="req-body">
                <div>
                    <h4 style="font-size:12px; color:#6b7280; margin-bottom:5px;">Fecha de Envío</h4>
                    <p style="font-weight:600; font-size:14px;"><?php echo date("d/m/Y, h:i a", strtotime($row['fecha_creacion'])); ?></p>
                </div>
                <div>
                    <h4 style="font-size:12px; color:#6b7280; margin-bottom:5px;">Documentos Adjuntos</h4>
                    <div class="doc-list">
                        <?php
                        // Buscar documentos de esta solicitud
                        $sql_docs = "SELECT tipo_documento, ruta_archivo FROM documentos_iniciales WHERE solicitud_id = $solicitud_id";
                        $docs_res = $conn->query($sql_docs);
                        while($doc = $docs_res->fetch_assoc()) {
                            // Link para ver el documento
                            echo '<a href="'.$doc['ruta_archivo'].'" target="_blank" class="doc-badge">';
                            echo '<i class="fas fa-paperclip"></i> ' . $doc['tipo_documento'];
                            echo '</a>';
                        }
                        ?>
                    </div>
                </div>
            </div>

            <form action="procesar_revision.php" method="POST">
                <input type="hidden" name="solicitud_id" value="<?php echo $solicitud_id; ?>">
                
                <div class="notes-area">
                    <h4 style="font-size:12px; color:#111827; margin-bottom:8px; font-weight:700;">Notas de Revisión</h4>
                    <textarea name="notas" rows="2" placeholder="Agregar notas sobre la revisión (obligatorio si se devuelve)..."></textarea>
                </div>

                <div class="req-footer">
                    <button type="button" class="btn-action btn-gray"><i class="fas fa-eye"></i> Ver Historial</button>
                    
                  <div class="footer-actions">
    <?php if ($row['estado_tramite'] == 'pendiente_aclaracion'): ?>
        
        <div style="display: flex; align-items: center; gap: 10px; width: 100%; justify-content: flex-end;">
            <span style="color: #d97706; font-weight: 600; font-size: 13px; background: #fffbeb; padding: 5px 10px; border-radius: 5px; border: 1px solid #fcd34d;">
                <i class="fas fa-hourglass-half"></i> Esperando archivo del ciudadano...
            </span>
            
            <button type="button" class="btn-action" style="background-color: #e5e7eb; color: #9ca3af; cursor: not-allowed;" disabled>
                <i class="fas fa-check-circle"></i> Aprobar
            </button>
            <button type="button" class="btn-action" style="background-color: #e5e7eb; color: #9ca3af; cursor: not-allowed;" disabled>
                <i class="fas fa-exclamation-circle"></i> Aclaración
            </button>
        </div>

    <?php else: ?>

        <button type="submit" name="accion" value="aprobar" class="btn-action btn-green">
            <i class="fas fa-check-circle"></i> Aprobar
        </button>
        
        <button type="submit" name="accion" value="aclaracion" class="btn-action btn-orange">
            <i class="fas fa-exclamation-circle"></i> Solicitar Aclaración
        </button>
        
        <button type="submit" name="accion" value="devolver" class="btn-action btn-red">
            <i class="fas fa-times-circle"></i> Devolver
        </button>

    <?php endif; ?>
</div>
                </div>
            </form>

        </div>
        <?php 
            } // Fin While
        } else {
            echo "<p style='text-align:center; color:#666; margin-top:50px;'>No hay solicitudes pendientes de revisión.</p>";
        }
        ?>

    </div>

</body>
</html>