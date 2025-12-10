<?php
$servername = "localhost";
$username = "root";       // Usuario por defecto de XAMPP
$password = "";           // Contraseña por defecto (vacía)
$dbname = "padron_migrante_db";

// Crear conexión
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
?>