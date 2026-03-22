<?php


ini_set('session.cookie_httponly', 1);
ini_set('session.use_only_cookies', 1);
session_start();

require_once "../config/conexion.php";

// Total equipos
$totalEquipos = $conexion->query("SELECT COUNT(*) as total FROM equipos")->fetch_assoc()['total'];

// Equipos con mantenimiento
$conMantenimiento = $conexion->query("SELECT COUNT(*) as total FROM equipos WHERE mantenimiento='SI'")->fetch_assoc()['total'];

// Equipos sin mantenimiento
$sinMantenimiento = $conexion->query("SELECT COUNT(*) as total FROM equipos WHERE mantenimiento='NO'")->fetch_assoc()['total'];

// Últimos equipos
$ultimos = $conexion->query("SELECT * FROM equipos ORDER BY id DESC LIMIT 5");

if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit;
}

$config = $conexion->query("SELECT * FROM configuracion LIMIT 1")->fetch_assoc();

$fechaRonda = $config['proxima_ronda'];
$equiposPorDia = $config['equipos_por_dia'];

$hoy = date('Y-m-d');
$diasRestantes = (strtotime($fechaRonda) - strtotime($hoy)) / 86400;

$equiposPendientes = $totalEquipos - $conMantenimiento;

$porDia = $diasRestantes > 0 ? ceil($equiposPendientes / $diasRestantes) : 0;

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<?php include "../includes/navbar.php"; ?>

<div class="container mt-4">

    <h3 class="mb-3">Bienvenida, <?= htmlspecialchars($_SESSION['usuario']) ?></h3>

    <!-- TARJETAS -->
    <div class="row">

        <div class="col-md-4">
            <div class="card bg-primary text-white shadow mb-3">
                <div class="card-body">
                    <h5>Total Equipos</h5>
                    <h2><?= $totalEquipos ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-success text-white shadow mb-3">
                <div class="card-body">
                    <h5>Con mantenimiento</h5>
                    <h2><?= $conMantenimiento ?></h2>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-warning text-dark shadow mb-3">
                <div class="card-body">
                    <h5>Plan de mantenimiento</h5>
                    <p>Fecha límite: <?= $fechaRonda ?></p>
                    <p>Equipos/día sugerido: <?= $porDia ?></p>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-danger text-white shadow mb-3">
                <div class="card-body">
                    <h5>Sin mantenimiento</h5>
                    <h2><?= $sinMantenimiento ?></h2>
                </div>
            </div>
        </div>

    </div>

    <!-- TABLA -->
    <div class="card shadow">
        <div class="card-body">
            <h5>Últimos equipos registrados</h5>

            <table class="table table-bordered mt-3">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Usuario</th>
                        <th>No. Serie</th>
                        <th>Área</th>
                        <th>Mantenimiento</th>
                    </tr>
                </thead>
                <tbody>

                <?php if ($ultimos->num_rows > 0): ?>
                    <?php while ($row = $ultimos->fetch_assoc()): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['id']) ?></td>
                            <td><?= htmlspecialchars($row['nombre_usuario']) ?></td>
                            <td><?= htmlspecialchars($row['numero_serie']) ?></td>
                            <td><?= htmlspecialchars($row['area']) ?></td>
                            <td>
                                <?php if ($row['mantenimiento'] == 'SI'): ?>
                                    <span class="badge bg-success">SI</span>
                                <?php else: ?>
                                    <span class="badge bg-danger">NO</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="5" class="text-center">No hay registros</td>
                    </tr>
                <?php endif; ?>

                </tbody>
            </table>

        </div>
    </div>

</div>

</body>
</html>