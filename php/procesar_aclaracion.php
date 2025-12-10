<?php
session_start();
include 'db.php'; // Debe ser PDO conectado a PostgreSQL (Supabase)

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $solicitud_id = isset($_POST['solicitud_id']) ? (int)$_POST['solicitud_id'] : 0;

    if ($solicitud_id <= 0) {
        echo "<script>alert('Solicitud inválida.'); window.history.back();</script>";
        exit();
    }

    // Validar archivo subido
    if (!isset($_FILES['archivo_aclaracion']) || $_FILES['archivo_aclaracion']['error'] !== UPLOAD_ERR_OK) {
        echo "<script>alert('Por favor selecciona un archivo válido.'); window.history.back();</script>";
        exit();
    }

    // Configuración de subida
    $directorio = "uploads/";
    $archivo = $_FILES['archivo_aclaracion'];
    $extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));

    // Extensiones permitidas
    $permitidas = ['pdf', 'jpg', 'jpeg', 'png'];
    if (!in_array($extension, $permitidas, true)) {
        echo "<script>alert('Formato no permitido. Usa PDF, JPG, JPEG o PNG.'); window.history.back();</script>";
        exit();
    }

    // Crear carpeta si no existe
    if (!is_dir($directorio)) {
        if (!mkdir($directorio, 0775, true)) {
            echo "<script>alert('No se pudo crear el directorio de cargas.'); window.history.back();</script>";
            exit();
        }
    }

    // Nombre único para el archivo
    $nombre_archivo = "aclaracion_" . $solicitud_id . "_" . time() . "." . $extension;
    $ruta_destino = $directorio . $nombre_archivo;

    // Mover archivo
    if (!move_uploaded_file($archivo['tmp_name'], $ruta_destino)) {
        echo "<script>alert('Error al guardar el archivo en el servidor.'); window.history.back();</script>";
        exit();
    }

    // Actualizar la solicitud: documento_aclaracion, estado, comentarios y fecha_ultimo_cambio
    try {
        $conn->beginTransaction();

        $sql = "
            UPDATE solicitudes
            SET documento_aclaracion = :ruta,
                estado_tramite = 'registro_inicial',
                ultimos_comentarios = 'El ciudadano ha enviado la evidencia solicitada. Pendiente de nueva revisión.',
                fecha_ultimo_cambio = CURRENT_TIMESTAMP
            WHERE id = :id
        ";

        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'ruta' => $ruta_destino,
            'id'   => $solicitud_id
        ]);

        // Verificar que se actualizó exactamente una fila
        if ($stmt->rowCount() !== 1) {
            $conn->rollBack();
            echo "<script>alert('No se encontró la solicitud o no se pudo actualizar.'); window.history.back();</script>";
            exit();
        }

        $conn->commit();

        echo "<script>
                alert('¡Archivo enviado correctamente! Tu solicitud ha vuelto a revisión.');
                window.location.href = 'mis_tramites.php';
              </script>";
        exit();

    } catch (PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        // Opcional: eliminar archivo si falla la BD para no dejar basura
        if (file_exists($ruta_destino)) {
            @unlink($ruta_destino);
        }
        echo "<script>alert('Error en la base de datos: " . htmlspecialchars($e->getMessage()) . "'); window.history.back();</script>";
        exit();
    }
}
?>
