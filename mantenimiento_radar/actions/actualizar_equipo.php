<?php
session_start();
require_once "../config/conexion.php";

// 🔒 Protección de sesión
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../pages/login.php");
    exit;
}

// Validar ID
if (!isset($_POST['id'])) {
    header("Location: ../pages/equipos.php");
    exit;
}

$id = $_POST['id'];

// Limpiar datos
$nombre_usuario = trim($_POST['nombre_usuario']);
$correo = trim($_POST['correo']);
$numero_serie = trim($_POST['numero_serie']);
$area = trim($_POST['area']);
$supervisor = trim($_POST['supervisor']);
$notas = trim($_POST['notas']);

// Validación básica
if (
    empty($nombre_usuario) ||
    empty($correo) ||
    empty($numero_serie)
) {
    header("Location: ../pages/equipos.php?error=campos");
    exit;
}


// Verificar duplicado (excepto el mismo registro)
$check = $conexion->prepare("SELECT id FROM equipos WHERE numero_serie = ? AND id != ?");
$check->bind_param("si", $numero_serie, $id);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    header("Location: ../pages/equipos.php?error=duplicado");
    exit;
}

// Update
$sql = "UPDATE equipos SET 
nombre_usuario=?, correo=?, numero_serie=?, area=?, supervisor=?, notas=?
WHERE id=?";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ssssssi",
    $nombre_usuario,
    $correo,
    $numero_serie,
    $area,
    $supervisor,
    $notas,
    $id
);

if ($stmt->execute()) {
    header("Location: ../pages/equipos.php?success=editado");
} else {
    echo "Error al actualizar: " . $stmt->error;
}