<?php
require_once "config/conexion.php";

$usuario = "Michelle";
$correo  = "admin@radar.com";
$password = password_hash("123456", PASSWORD_DEFAULT);
$rol = "tecnico";
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