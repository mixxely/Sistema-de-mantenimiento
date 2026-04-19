<?php
session_start();
require_once "../config/conexion.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../pages/login.php");
    exit;
}

$modo = $_POST['modo'];

// actualizar configuración
$sql = "UPDATE configuracion SET modo_ronda = ?";
$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $modo);
$stmt->execute();

header("Location: ../pages/dashboard.php");