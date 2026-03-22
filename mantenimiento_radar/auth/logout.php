<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();


// Vaciar variables de sesión
session_unset();

// Destruir la sesión
session_destroy();

// Redirigir al login
header("Location: ../pages/login.php");
exit;

