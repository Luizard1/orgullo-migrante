<?php
session_start();
include 'db.php'; // Debe ser PDO conectado a PostgreSQL (Supabase)

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.html");
    exit();
}

$usuario_id   = $_SESSION['usuario_id'];
$tipo_tramite = $_POST['tipo_tramite'] ?? 'registro_inicial'; // opcional: guardar en columna aparte

try {
    // 1. Crear nueva solicitud en la BD
    $sql_solicitud = "INSERT INTO solicitudes (usuario_id, estado_tramite, fecha_creacion, fecha_ultimo_cambio)
                      VALUES (:usuario_id, 'registro_inicial', CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)";
    $stmt = $conn->prepare($sql_solicitud);
    $stmt->execute(['usuario_id' => $usuario_id]);

    // Obtener el ID generado
    $solicitud_id = $conn->lastInsertId();

    // Crear carpeta de subidas si no existe
    $target_dir = "uploads/" . $solicitud_id . "/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Función para guardar archivo y registrar en documentos_iniciales
    function guardarDocumento($conn, $fileInputName, $tipoDoc, $solicitudId, $targetDir) {
        if (!empty($_FILES[$fileInputName]['name'])) {
            $fileName        = basename($_FILES[$fileInputName]['name']);
            $targetFilePath  = $targetDir . $fileName;

            if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $targetFilePath)) {
                $sql_doc = "INSERT INTO documentos_iniciales (solicitud_id, tipo_documento, ruta_archivo)
                            VALUES (:solicitud_id, :tipo_documento, :ruta_archivo)";
                $stmt_doc = $conn->prepare($sql_doc);
                $stmt_doc->execute([
                    'solicitud_id'   => $solicitudId,
                    'tipo_documento' => $tipoDoc,
                    'ruta_archivo'   => $targetFilePath
                ]);
            }
        }
    }

    // Guardar cada archivo
    guardarDocumento($conn, 'archivo_curp', 'CURP', $solicitud_id, $target_dir);
    guardarDocumento($conn, 'archivo_rfc', 'RFC', $solicitud_id, $target_dir);
    guardarDocumento($conn, 'archivo_domicilio', 'ComprobanteDomicilio', $solicitud_id, $target_dir);
    guardarDocumento($conn, 'archivo_evidencia', 'EvidenciaAdicional', $solicitud_id, $target_dir);

    echo "<script>alert('Solicitud enviada correctamente. Folio #$solicitud_id'); window.location.href='dashboard_ciudadano.php';</script>";
    exit();

} catch (PDOException $e) {
    echo "<script>alert('Error en la base de datos: " . htmlspecialchars($e->getMessage()) . "'); window.history.back();</script>";
    exit();
}
?>
