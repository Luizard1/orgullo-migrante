<?php
session_start();
include 'db.php';

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: index.html");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    
    $solicitud_id = $_POST['solicitud_id'];
    
    // Validar que se haya subido un archivo
    if (isset($_FILES['archivo_aclaracion']) && $_FILES['archivo_aclaracion']['error'] == 0) {
        
        // 1. Configuración de la carpeta de destino
        $directorio = "uploads/"; // Asegúrate de que esta carpeta exista en tu servidor
        
        // Generamos un nombre único para no sobrescribir otros archivos
        $extension = pathinfo($_FILES['archivo_aclaracion']['name'], PATHINFO_EXTENSION);
        $nombre_archivo = "aclaracion_" . $solicitud_id . "_" . time() . "." . $extension;
        $ruta_destino = $directorio . $nombre_archivo;

        // 2. Mover el archivo de la carpeta temporal a la carpeta final
        if (move_uploaded_file($_FILES['archivo_aclaracion']['tmp_name'], $ruta_destino)) {
            
            // 3. ACTUALIZAR BASE DE DATOS (El paso que desbloquea al revisor)
            // Cambiamos estado de 'pendiente_aclaracion' -> 'registro_inicial'
            // Y actualizamos la fecha del último cambio
            
            $sql = "UPDATE solicitudes 
                    SET documento_aclaracion = '$ruta_destino', 
                        estado_tramite = 'registro_inicial', 
                        ultimos_comentarios = 'El ciudadano ha enviado la evidencia solicitada. Pendiente de nueva revisión.',
                        fecha_ultimo_cambio = NOW()
                    WHERE id = '$solicitud_id'";

            if ($conn->query($sql) === TRUE) {
                echo "<script>
                        alert('¡Archivo enviado correctamente! Tu solicitud ha vuelto a revisión.');
                        window.location.href = 'mis_tramites.php';
                      </script>";
            } else {
                echo "Error en la base de datos: " . $conn->error;
            }

        } else {
            echo "<script>alert('Error al guardar el archivo en el servidor.'); window.history.back();</script>";
        }
    } else {
        echo "<script>alert('Por favor selecciona un archivo válido.'); window.history.back();</script>";
    }
}
?>