<?php
session_start();
include 'db.php'; // Debe ser PDO conectado a PostgreSQL (Supabase)

// Seguridad: Solo revisores pueden entrar aquí
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.html");
    exit();
}

// Recibir datos del formulario del panel_revisor.php
$solicitud_id = isset($_POST['solicitud_id']) ? (int)$_POST['solicitud_id'] : 0;
$accion       = $_POST['accion'] ?? '';
$notas        = trim($_POST['notas'] ?? '');

$nuevo_estado   = '';
$mensaje_alerta = '';

// Lógica de los botones
switch ($accion) {
    case 'aprobar':
        $nuevo_estado   = 'listo_activacion';
        $mensaje_alerta = "Solicitud APROBADA correctamente.";
        break;

    case 'aclaracion':
        $nuevo_estado   = 'pendiente_aclaracion';
        $mensaje_alerta = "Se ha solicitado una aclaración al ciudadano.";
        if ($notas === '') {
            echo "<script>alert('Error: Debes escribir una nota explicando qué aclaración necesitas.'); window.history.back();</script>";
            exit();
        }
        break;

    case 'devolver':
        $nuevo_estado   = 'devuelto'; // asegúrate de que este valor esté permitido en tu CHECK de estado_tramite
        $mensaje_alerta = "El trámite ha sido marcado como DEVUELTO.";
        if ($notas === '') {
            echo "<script>alert('Error: Para devolver un trámite debes explicar la razón en las notas.'); window.history.back();</script>";
            exit();
        }
        break;

    default:
        echo "<script>alert('Acción no reconocida.'); window.history.back();</script>";
        exit();
}

// Actualizar la Base de Datos en Supabase
if ($nuevo_estado !== '' && $solicitud_id > 0) {
    try {
        $sql = "
            UPDATE solicitudes
            SET estado_tramite = :estado,
                fecha_ultimo_cambio = CURRENT_TIMESTAMP,
                ultimos_comentarios = :notas
            WHERE id = :id
        ";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'estado' => $nuevo_estado,
            'notas'  => $notas,
            'id'     => $solicitud_id
        ]);

        if ($stmt->rowCount() === 1) {
            echo "<script>
                    alert('$mensaje_alerta');
                    window.location.href='panel_revisor.php';
                  </script>";
            exit();
        } else {
            echo "<script>alert('No se encontró la solicitud o no se pudo actualizar.'); window.history.back();</script>";
            exit();
        }
    } catch (PDOException $e) {
        echo "<script>alert('Error al actualizar: " . htmlspecialchars($e->getMessage()) . "'); window.history.back();</script>";
        exit();
    }
}
?>
