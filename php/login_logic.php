<?php
session_start();
include 'db.php';

$email = $_POST['email'];
$password = $_POST['password'];

// Buscar usuario por email
$sql = "SELECT id, nombre_completo, password_hash, rol FROM usuarios WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    // Verificar contraseña
    if (password_verify($password, $row['password_hash'])) {
        // Guardar datos en sesión
        $_SESSION['usuario_id'] = $row['id'];
        $_SESSION['nombre'] = $row['nombre_completo'];
        $_SESSION['rol'] = $row['rol'];

        // Redirigir según el rol (Aquí crearás estas páginas después)
        if ($row['rol'] == 'revisor') {
    // Si es revisor, va a su panel especial
    echo "<script>window.location.href='panel_revisor.php';</script>";
} elseif ($row['rol'] == 'supervisor') {
    echo "<script>window.location.href='panel_supervisor.php';</script>";
} else {
    // Si es ciudadano, va al dashboard normal
    echo "<script>window.location.href='dashboard_ciudadano.php';</script>";
}
    } else {
        echo "<script>alert('Contraseña incorrecta'); window.location.href='index.html';</script>";
    }
} else {
    echo "<script>alert('El usuario no existe'); window.location.href='index.html';</script>";
}

$stmt->close();
$conn->close();
?>