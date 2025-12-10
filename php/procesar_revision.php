<?php
session_start();
include 'db.php';

// Seguridad: Solo revisores pueden entrar aquí
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.html");
    exit();
}

// Recibir datos del formulario del panel_revisor.php
$solicitud_id = $_POST['solicitud_id'];
$accion = $_POST['accion']; // aprobar, aclaracion, devolver
$notas = $_POST['notas'];   // El texto del textarea

$nuevo_estado = "";
$mensaje_alerta = "";

// Lógica de los botones
switch ($accion) {
    case 'aprobar':
        $nuevo_estado = 'listo_activacion'; 
        $mensaje_alerta = "Solicitud APROBADA correctamente.";
        break;

    case 'aclaracion':
        $nuevo_estado = 'pendiente_aclaracion';
        $mensaje_alerta = "Se ha solicitado una aclaración al ciudadano.";
        
        // Validación: Exigir nota obligatoria
        if (empty(trim($notas))) {
            echo "<script>alert('Error: Debes escribir una nota explicando qué aclaración necesitas.'); window.history.back();</script>";
            exit();
        }
        break;

    case 'devolver':
        // --- CAMBIO IMPORTANTE AQUÍ ---
        // Cambiamos a 'devuelto' para que salga de tu lista de pendientes
        $nuevo_estado = 'devuelto'; 
        $mensaje_alerta = "El trámite ha sido marcado como DEVUELTO.";
        
        if (empty(trim($notas))) {
            echo "<script>alert('Error: Para devolver un trámite debes explicar la razón en las notas.'); window.history.back();</script>";
            exit();
        }
        break;
}

// Actualizar la Base de Datos
if ($nuevo_estado != "") {
    // Actualizamos Estado, Fecha de Cambio y guardamos las Notas
    $sql = "UPDATE solicitudes 
            SET estado_tramite = ?, 
                fecha_ultimo_cambio = NOW(), 
                ultimos_comentarios = ? 
            WHERE id = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $nuevo_estado, $notas, $solicitud_id);

    if ($stmt->execute()) {
        // Éxito: Regresar al panel
        echo "<script>
                alert('$mensaje_alerta'); 
                window.location.href='panel_revisor.php';
              </script>";
    } else {
        echo "Error al actualizar: " . $conn->error;
    }
    
    $stmt->close();
}
$conn->close();
?>