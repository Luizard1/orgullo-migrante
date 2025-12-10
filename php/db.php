<?php

// Datos de conexión a Supabase (PostgreSQL)
$host = "https://njfgquifcdhoakaeqrqz.supabase.co";   // ejemplo: db.abcd1234.supabase.co
$port = "5432";
$dbname = "padron_migrante";            // normalmente es 'postgres' o el nombre que definiste
$user = "Luizard1";            // el usuario que te da Supabase
$password = "orgullomigrante";       // la contraseña que te da Supabase

try {
    // Crear conexión con PDO
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conexión exitosa a Supabase";
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}







//------------VERSION CON CONEXION LOCAL-------------
//$servername = "localhost";
//$username = "root";       // Usuario por defecto de XAMPP
//$password = "";           // Contraseña por defecto (vacía)
//$dbname = "padron_migrante_db";

// Crear conexión
//$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar conexión
//if ($conn->connect_error) {
//    die("Error de conexión: " . $conn->connect_error);
//}








?>
