<?php
session_start();
require_once "../config/conexion.php";

// Seguridad
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../pages/login.php");
    exit;
}

// Validar ID
if (!isset($_GET['id'])) {
    header("Location: ../pages/equipos.php");
    exit;
}

$id = $_GET['id'];

// Eliminar equipo
$sql = "DELETE FROM equipos WHERE id = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $id);

if ($stmt->execute()) {
    header("Location: ../pages/equipos.php?success=eliminado");
} else {
    echo "Error al eliminar";
}