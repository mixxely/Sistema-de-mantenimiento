<?php
session_start();
require_once "../config/conexion.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../pages/login.php");
    exit;
}

if (!isset($_GET['id'])) {
    header("Location: ../pages/equipos.php");
    exit;
}

$id = $_GET['id'];

// 🔴 PRIMERO eliminar mantenimientos
$stmt1 = $conexion->prepare("DELETE FROM mantenimientos WHERE equipo_id = ?");
$stmt1->bind_param("i", $id);
$stmt1->execute();

// 🔴 DESPUÉS eliminar equipo
$stmt2 = $conexion->prepare("DELETE FROM equipos WHERE id = ?");
$stmt2->bind_param("i", $id);

if ($stmt2->execute()) {
    header("Location: ../pages/equipos.php?success=eliminado");
    exit;
} else {
    echo "Error al eliminar: " . $stmt2->error;
}