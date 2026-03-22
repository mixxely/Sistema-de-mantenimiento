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
$autorizado = $_POST['autorizado'];
$notas = $_POST['notas'] ?? "";

// Calcular próximo mantenimiento (ej: +3 meses)
$proximo = date('Y-m-d', strtotime($fecha . ' +3 months'));

// INSERT en historial
$sql = "INSERT INTO mantenimientos 
(equipo_id, fecha, tipo, autorizado, notas, proximo_mantenimiento)
VALUES (?, ?, ?, ?, ?, ?)";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("ississ", $id, $fecha, $tipo, $autorizado, $notas, $proximo);

$stmt->execute();

// Opcional: actualizar estado en equipos
$conexion->query("UPDATE equipos SET mantenimiento='SI' WHERE id=$id");


header("Location: ../pages/equipos.php?success=mantenimiento");