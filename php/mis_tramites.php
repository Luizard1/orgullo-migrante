<?php
session_start();
include 'db.php'; // Debe ser PDO a PostgreSQL (Supabase)

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.html");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$nombre_usuario = $_SESSION['nombre'];

try {
    // 1. Total de Trámites
    $stmt_total = $conn->prepare("SELECT COUNT(*) AS total FROM solicitudes WHERE usuario_id = :usuario_id");
    $stmt_total->execute(['usuario_id' => $usuario_id]);
    $total_tramites = (int)($stmt_total->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

    // 2. Aprobados (Estado: listo_activacion o activo)
    $stmt_aprobados = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM solicitudes
        WHERE usuario_id = :usuario_id
          AND (estado_tramite = 'activo' OR estado_tramite = 'listo_activacion')
    ");
    $stmt_aprobados->execute(['usuario_id' => $usuario_id]);
    $total_aprobados = (int)($stmt_aprobados->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

    // 3. En Revisión (Estado: registro_inicial)
    $stmt_revision = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM solicitudes
        WHERE usuario_id = :usuario_id
          AND estado_tramite = 'registro_inicial'
    ");
    $stmt_revision->execute(['usuario_id' => $usuario_id]);
    $total_revision = (int)($stmt_revision->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

    // 4. Pendientes (Estado: pendiente_aclaracion)
    $stmt_pendientes = $conn->prepare("
        SELECT COUNT(*) AS total
        FROM solicitudes
        WHERE usuario_id = :usuario_id
          AND estado_tramite = 'pendiente_aclaracion'
    ");
    $stmt_pendientes->execute(['usuario_id' => $usuario_id]);
    $total_pendientes = (int)($stmt_pendientes->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

    // 5. Listado de trámites del usuario (ordenados por fecha_creacion DESC)
    $stmt_tramites = $conn->prepare("
        SELECT id, fecha_creacion, fecha_ultimo_cambio, ultimos_comentarios, estado_tramite
        FROM solicitudes
        WHERE usuario_id = :usuario_id
        ORDER BY fecha_creacion DESC
    ");
    $stmt_tramites->execute(['usuario_id' => $usuario_id]);
    $tramites = $stmt_tramites->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Error en las consultas: " . htmlspecialchars($e->getMessage()));
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Padrón Orgullo Migrante - Panel</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
</head>
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
            <button class="tab-btn" onclick="window.location.href='dashboard_ciudadano.php'">⬆ Cargar Documentos</button>
            <button class="tab-btn active">📄 Mis Trámites</button>
            <button class="tab-btn">🔔 Notificaciones <span class="badge">3</span></button>
        </div>

        <div id="lista-tramites" style="margin-top: 40px;">
            <h2 style="margin-bottom: 20px;">Mis Trámites Recientes</h2>

            <?php if (!empty($tramites)) : ?>
                <?php foreach ($tramites as $row) :
                    $folio = "POM-2025-" . str_pad($row['id'], 5, "0", STR_PAD_LEFT);
                    $fecha_sol = $row['fecha_creacion'] ? date("d/m/Y", strtotime($row['fecha_creacion'])) : '—';
                    $fecha_act = $row['fecha_ultimo_cambio'] ? date("d/m/Y", strtotime($row['fecha_ultimo_cambio'])) : '—';
                    $comentario = $row['ultimos_comentarios'] ?? '';

                    $estado = strtolower(trim($row['estado_tramite'] ?? ''));
                    $estado_texto = "Devuelto";
                    $clase_color = "bg-red";
                    $icono = "times-circle";
                    $msg_default = "El trámite ha sido devuelto. Lee las notas abajo.";

                    switch ($estado) {
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

                        case 'baja':
                        case 'devuelto':
                            $estado_texto = "Devuelto";
                            $clase_color = "bg-red";
                            $icono = "times-circle";
                            $msg_default = "El trámite ha sido devuelto. Lee las notas abajo.";
                            break;

                        default:
                            // mantiene valores por defecto "Devuelto"
                            break;
                    }
                ?>
                <div class="tramite-card">
                    <div class="tramite-header">
                        <div>
                            <div class="tramite-title">Registro Padrón Orgullo Migrante</div>
                            <div class="tramite-folio">Folio: <?php echo htmlspecialchars($folio); ?></div>
                        </div>
                        <div class="status-pill <?php echo htmlspecialchars($clase_color); ?>">
                            <i class="fas fa-<?php echo htmlspecialchars($icono); ?>"></i> <?php echo htmlspecialchars($estado_texto); ?>
                        </div>
                    </div>

                    <div class="tramite-fechas">
                        <div>
                            <div class="fecha-label">Fecha de Solicitud</div>
                            <div class="fecha-valor"><?php echo htmlspecialchars($fecha_sol); ?></div>
                        </div>
                        <div>
                            <div class="fecha-label">Última Actualización</div>
                            <div class="fecha-valor"><?php echo htmlspecialchars($fecha_act); ?></div>
                        </div>
                    </div>

                    <div class="mensaje-revisor">
                        <i class="fas fa-info-circle mensaje-icon"></i>
                        <div>
                            <?php
                            if (!empty($comentario)) {
                                echo "<strong>Nota del Revisor:</strong> " . htmlspecialchars($comentario);
                            } else {
                                echo htmlspecialchars($msg_default);
                            }
                            ?>
                        </div>
                    </div>

                    <?php if ($estado === 'pendiente_aclaracion') : ?>
                        <div style="margin-top: 15px; border-top: 1px solid #eee; padding-top: 15px;">
                            <p style="font-size: 13px; color: #f97316; font-weight: 600; margin-bottom: 5px;">Subir Aclaración:</p>
                            <form action="procesar_aclaracion.php" method="POST" enctype="multipart/form-data" style="display:flex; gap:10px;">
                                <input type="hidden" name="solicitud_id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                <input type="file" name="archivo_aclaracion" required style="font-size: 12px;">
                                <button type="submit" class="submit-btn" style="width: auto; padding: 5px 15px; background: #f97316;">Enviar</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            <?php else : ?>
                <p style="color:#666;">Aún no tienes trámites registrados.</p>
            <?php endif; ?>
        </div>

    </div>

</body>
</html>
