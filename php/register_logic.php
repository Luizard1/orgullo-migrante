<?php
include 'db.php';

// Recibir datos del formulario
$nombre = $_POST['fullname'];
$email = $_POST['email'];
$password = $_POST['password'];

// Encriptar contraseña (seguridad básica)
$password_hash = password_hash($password, PASSWORD_DEFAULT);

// Preparar la consulta SQL para evitar hackeos
$sql = "INSERT INTO usuarios (nombre_completo, email, password_hash, rol) VALUES (?, ?, ?, 'ciudadano')";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sss", $nombre, $email, $password_hash);

if ($stmt->execute()) {
    // Si se registra bien, mandar al login
    echo "<script>alert('Registro exitoso. Ahora inicia sesión.'); window.location.href='index.html';</script>";
} else {
    echo "Error: " . $conn->error;
}

$stmt->close();
$conn->close();
?>