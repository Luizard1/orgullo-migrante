<?php
session_start();
include 'db.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.html");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];
$tipo_tramite = $_POST['tipo_tramite']; // Esto lo podrías guardar en una columna nueva si quisieras, por ahora lo usamos para lógica

// 1. Crear una nueva solicitud en la BD
$sql_solicitud = "INSERT INTO solicitudes (usuario_id, estado_tramite) VALUES (?, 'registro_inicial')";
$stmt = $conn->prepare($sql_solicitud);
$stmt->bind_param("i", $usuario_id);

if ($stmt->execute()) {
    $solicitud_id = $stmt->insert_id; // Obtenemos el ID del folio creado
    
    // Crear carpeta de subidas si no existe
    $target_dir = "uploads/" . $solicitud_id . "/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    // Función para guardar archivo
    function guardarDocumento($conn, $fileInputName, $tipoDoc, $solicitudId, $targetDir) {
        if (!empty($_FILES[$fileInputName]['name'])) {
            $fileName = basename($_FILES[$fileInputName]['name']);
            $targetFilePath = $targetDir . $fileName;
            
            if (move_uploaded_file($_FILES[$fileInputName]['tmp_name'], $targetFilePath)) {
                $sql_doc = "INSERT INTO documentos_iniciales (solicitud_id, tipo_documento, ruta_archivo) VALUES (?, ?, ?)";
                $stmt_doc = $conn->prepare($sql_doc);
                $stmt_doc->bind_param("iss", $solicitudId, $tipoDoc, $targetFilePath);
                $stmt_doc->execute();
            }
        }
    }

    // Guardar cada archivo
    guardarDocumento($conn, 'archivo_curp', 'CURP', $solicitud_id, $target_dir);
    guardarDocumento($conn, 'archivo_rfc', 'RFC', $solicitud_id, $target_dir);
    guardarDocumento($conn, 'archivo_domicilio', 'ComprobanteDomicilio', $solicitud_id, $target_dir);
    guardarDocumento($conn, 'archivo_evidencia', 'EvidenciaAdicional', $solicitud_id, $target_dir);

    echo "<script>alert('Solicitud enviada correctamente. Folio #$solicitud_id'); window.location.href='dashboard_ciudadano.php';</script>";

} else {
    echo "Error: " . $conn->error;
}

$stmt->close();
$conn->close();
?>