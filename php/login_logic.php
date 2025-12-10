<?php
session_start();
include 'db.php'; // Este db.php ya debe estar configurado con PDO para Supabase

$email = $_POST['email'];
$password = $_POST['password'];

try {
    // Buscar usuario por email con consulta preparada
    $sql = "SELECT id, nombre_completo, password_hash, rol FROM usuarios WHERE email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['email' => $email]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row) {
        // Verificar contraseña
        if (password_verify($password, $row['password_hash'])) {
            // Guardar datos en sesión
            $_SESSION['usuario_id'] = $row['id'];
            $_SESSION['nombre'] = $row['nombre_completo'];
            $_SESSION['rol'] = $row['rol'];

            // Redirigir según el rol
            if ($row['rol'] === 'revisor') {
                echo "<script>window.location.href='panel_revisor.php';</script>";
            } elseif ($row['rol'] === 'supervisor') {
                echo "<script>window.location.href='panel_supervisor.php';</script>";
            } else {
                echo "<script>window.location.href='dashboard_ciudadano.php';</script>";
            }
        } else {
            echo "<script>alert('Contraseña incorrecta'); window.location.href='index.html';</script>";
        }
    } else {
        echo "<script>alert('El usuario no existe'); window.location.href='index.html';</script>";
    }
} catch (PDOException $e) {
    die("Error en la consulta: " . $e->getMessage());
}
?>
