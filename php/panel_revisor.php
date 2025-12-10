<?php
session_start();
include 'db.php'; // Debe ser PDO a PostgreSQL (Supabase)

// Verificar sesión y rol (opcional: exigir revisor)
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.html");
    exit();
}
// Si quieres forzar que sea revisor, descomenta:
// if ($_SESSION['rol'] !== 'revisor') { header("Location: index.html"); exit(); }

$nombre_revisor = $_SESSION['nombre'] ?? '';

// --- ESTADÍSTICAS GLOBALES ---
try {
    // Totales
    $stmt_total = $conn->query("SELECT COUNT(*) AS c FROM solicitudes");
    $total_solicitudes = (int)($stmt_total->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);

    // En Revisión
    $stmt_rev = $conn->prepare("SELECT COUNT(*) AS c FROM solicitudes WHERE estado_tramite = 'registro_inicial'");
    $stmt_rev->execute();
    $total_revision = (int)($stmt_rev->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);

    // Pendientes (Aclaraciones)
    $stmt_pend = $conn->prepare("SELECT COUNT(*) AS c FROM solicitudes WHERE estado_tramite = 'pendiente_aclaracion'");
    $stmt_pend->execute();
    $total_pendientes = (int)($stmt_pend->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);

    // Revisadas Hoy (cualquier cambio de estado hoy y ya no en registro_inicial)
    $hoy = date('Y-m-d');
    $stmt_hoy = $conn->prepare("
        SELECT COUNT(*) AS c
        FROM solicitudes
        WHERE fecha_ultimo_cambio::date = :hoy
          AND estado_tramite <> 'registro_inicial'
    ");
    $stmt_hoy->execute(['hoy' => $hoy]);
    $total_hoy = (int)($stmt_hoy->fetch(PDO::FETCH_ASSOC)['c'] ?? 0);

    // Solicitudes pendientes (registro_inicial o pendiente_aclaracion), con nombre de usuario
    $stmt_pendientes_list = $conn->prepare("
        SELECT s.id, u.nombre_completo, s.fecha_creacion, s.estado_tramite
        FROM solicitudes s
        JOIN usuarios u ON s.usuario_id = u.id
        WHERE s.estado_tramite IN ('registro_inicial', 'pendiente_aclaracion')
        ORDER BY s.fecha_creacion DESC
    ");
    $stmt_pendientes_list->execute();
    $solicitudes_pendientes = $stmt_pendientes_list->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en las consultas: " . htmlspecialchars($e->getMessage()));
}
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

        <?php if (!empty($solicitudes_pendientes)) : ?>
            <?php foreach ($solicitudes_pendientes as $row) :
                $folio = "POM-2025-" . str_pad($row['id'], 5, "0", STR_PAD_LEFT);
                $solicitud_id = $row['id'];
            ?>
            <div class="request-card">
                <div class="req-header">
                    <div class="req-title">
                        <h3>Actualización de Datos Personales</h3>
                        <div class="req-subtitle">
                            Folio: <strong><?php echo htmlspecialchars($folio); ?></strong> • Ciudadano: <?php echo htmlspecialchars($row['nombre_completo']); ?>
                        </div>
                    </div>
                    <div class="status-badge">
                        <i class="fas fa-file-alt"></i> En Revisión
                    </div>
                </div>

                <div class="req-body">
                    <div>
                        <h4 style="font-size:12px; color:#6b7280; margin-bottom:5px;">Fecha de Envío</h4>
                        <p style="font-weight:600; font-size:14px;"><?php echo htmlspecialchars(date("d/m/Y, h:i a", strtotime($row['fecha_creacion']))); ?></p>
                    </div>
                    <div>
                        <h4 style="font-size:12px; color:#6b7280; margin-bottom:5px;">Documentos Adjuntos</h4>
                        <div class="doc-list">
                            <?php
                            // Buscar documentos de esta solicitud
                            try {
                                $stmt_docs = $conn->prepare("
                                    SELECT tipo_documento, ruta_archivo
                                    FROM documentos_iniciales
                                    WHERE solicitud_id = :solicitud_id
                                ");
                                $stmt_docs->execute(['solicitud_id' => $solicitud_id]);
                                $docs = $stmt_docs->fetchAll(PDO::FETCH_ASSOC);

                                foreach ($docs as $doc) {
                                    echo '<a href="'.htmlspecialchars($doc['ruta_archivo']).'" target="_blank" class="doc-badge">';
                                    echo '<i class="fas fa-paperclip"></i> ' . htmlspecialchars($doc['tipo_documento']);
                                    echo '</a>';
                                }
                            } catch (PDOException $e) {
                                echo '<span style="color:#dc2626;">Error al cargar documentos.</span>';
                            }
                            ?>
                        </div>
                    </div>
                </div>

                <form action="procesar_revision.php" method="POST">
                    <input type="hidden" name="solicitud_id" value="<?php echo htmlspecialchars($solicitud_id); ?>">

                    <div class="notes-area">
                        <h4 style="font-size:12px; color:#111827; margin-bottom:8px; font-weight:700;">Notas de Revisión</h4>
                        <textarea name="notas" rows="2" placeholder="Agregar notas sobre la revisión (obligatorio si se devuelve)..."></textarea>
                    </div>

                    <div class="req-footer">
                        <button type="button" class="btn-action btn-gray"><i class="fas fa-eye"></i> Ver Historial</button>

                        <div class="footer-actions">
                            <?php if ($row['estado_tramite'] === 'pendiente_aclaracion') : ?>
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
                            <?php else : ?>
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
            <?php endforeach; ?>
        <?php else : ?>
            <p style='text-align:center; color:#666; margin-top:50px;'>No hay solicitudes pendientes de revisión.</p>
        <?php endif; ?>

    </div>

</body>
</html>
