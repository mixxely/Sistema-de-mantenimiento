<?php
session_start();
require_once "../config/conexion.php";

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../pages/login.php");
    exit;
}

$id = $_POST['id'];
$fecha = $_POST['fecha'];
$tipo = $_POST['tipo'];
$autorizado = $_POST['autorizado'] ?? 0;
$notas = $_POST['notas'] ?? "";

// 🔹 Obtener meses desde configuración
$config = $conexion->query("SELECT meses_mantenimiento FROM configuracion LIMIT 1")->fetch_assoc();
$meses = $config['meses_mantenimiento'];

// 🔹 Calcular próximo mantenimiento
$proximo = date('Y-m-d', strtotime($fecha . " +$meses months"));

// INSERT en historial
$sql = "INSERT INTO mantenimientos 
(equipo_id, fecha, tipo, autorizado, notas, proximo_mantenimiento)
VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ississ", $id, $fecha, $tipo, $autorizado, $notas, $proximo);


$stmt->execute();




header("Location: ../pages/equipos.php?success=mantenimiento");