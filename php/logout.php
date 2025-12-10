<?php
session_start(); // Inicia la sesión actual para poder destruirla
session_unset(); // Borra todas las variables de sesión
session_destroy(); // Destruye la sesión completamente

// Te regresa a la pantalla de inicio (Login)
header("Location: index.html");
exit();
?>