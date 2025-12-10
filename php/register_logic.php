<?php
include 'db.php'; // Debe ser PDO conectado a PostgreSQL (Supabase)

// Recibir datos del formulario
$nombre   = $_POST['fullname'] ?? '';
$email    = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

// Validar datos básicos
if ($nombre === '' || $email === '' || $password === '') {
    echo "<script>alert('Todos los campos son obligatorios.'); window.history.back();</script>";
    exit();
}

// Encriptar contraseña (seguridad básica)
$password_hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // Preparar la consulta SQL con parámetros nombrados
    $sql = "INSERT INTO usuarios (nombre_completo, email, password_hash, rol, fecha_registro)
            VALUES (:nombre, :email, :password_hash, 'ciudadano', CURRENT_TIMESTAMP)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([
        'nombre'        => $nombre,
        'email'         => $email,
        'password_hash' => $password_hash
    ]);

    echo "<script>alert('Registro exitoso. Ahora inicia sesión.'); window.location.href='index.html';</script>";
    exit();

} catch (PDOException $e) {
    // Manejo de errores (ejemplo: email duplicado)
    $errorMsg = $e->getMessage();
    if (strpos($errorMsg, 'usuarios_email_key') !== false) {
        echo "<script>alert('El correo ya está registrado. Usa otro.'); window.history.back();</script>";
    } else {
        echo "<script>alert('Error en la base de datos: " . htmlspecialchars($errorMsg) . "'); window.history.back();</script>";
    }
    exit();
}
?>
