<?php
ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();


require_once "../config/conexion.php";

$usuario  = $_POST['usuario'];
$password = $_POST['password'];

$sql = "SELECT * FROM usuarios WHERE usuario = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $user = $resultado->fetch_assoc();

    if (password_verify($password, $user['password'])) {

        session_regenerate_id(true);

        $_SESSION['id_usuario'] = $user['id'];
        $_SESSION['usuario']    = $user['usuario'];
        $_SESSION['rol']        = $user['rol'];

        header("Location: ../pages/dashboard.php");
        exit;
    }
}


header("Location: ../pages/login.php?error=credenciales");
exit;