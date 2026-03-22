<?php
require_once "config/conexion.php";

$usuario = "admin";
$correo  = "admin@radar.com";
$password = password_hash("123456", PASSWORD_DEFAULT);
$rol = "supervisor";
$estado = "activo";

$sql = "INSERT INTO usuarios (usuario, correo, password, rol, estado)
        VALUES (?, ?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("sssss", $usuario, $correo, $password, $rol, $estado);

if ($stmt->execute()) {
    echo "Usuario creado correctamente";
} else {
    echo "Error: " . $stmt->error;
}