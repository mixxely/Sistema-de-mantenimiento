<?php
session_start();
require_once "../config/conexion.php";

// Proteger acceso
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../pages/login.php");
    exit;
}

// 1️⃣ Recibir datos del formulario
$nombre_usuario = trim($_POST['nombre_usuario']);
$correo         = trim($_POST['correo']);
$numero_serie   = trim($_POST['numero_serie']);
$area           = trim($_POST['area']);
$supervisor     = trim($_POST['supervisor']);
$notas          = trim($_POST['notas']);

// 2️⃣ Validaciones obligatorias
if (
    empty($nombre_usuario) ||
    empty($correo) ||
    empty($numero_serie) ||
    empty($area)
) {
    header("Location: ../pages/equipo_nuevo.php?error=campos");
    exit;
}

// 3️⃣ Verificar que el número de serie no exista
$check = $conexion->prepare("SELECT id FROM equipos WHERE numero_serie = ?");
$check->bind_param("s", $numero_serie);
$check->execute();
$check->store_result();

if ($check->num_rows > 0) {
    header("Location: ../pages/equipo_nuevo.php?error=duplicado");
    exit;
}

// 4️⃣ Insertar equipo
$sql = "INSERT INTO equipos 
(nombre_usuario, correo, numero_serie, area, supervisor, notas)
VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);
$stmt->bind_param(
    "ssssss",
    $nombre_usuario,
    $correo,
    $numero_serie,
    $area,
    $supervisor,
    $notas
);

if ($stmt->execute()) {
    header("Location: ../pages/equipos.php?success=1");
    exit;
} else {
    header("Location: ../pages/equipo_nuevo.php?error=bd");
    exit;
}